<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\InteractsWithScopedApiTokens;
use App\Http\Resources\NotificationResource;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\Notification as ModelNotification;
use App\Http\Requests\Api\Notifications\SendToAllUsersNotificationRequest;
use App\Http\Requests\Api\Notifications\SendToUsersNotificationRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class NotificationController extends Controller
{
    use InteractsWithScopedApiTokens;

    public function __construct(protected NotificationService $notificationService)
    {}

    /**
     * Show a list of notifications for a user.
     *
     * @param  Request  $request
     * @param  User  $user
     * @return NotificationResource
     * 
     * @throws ModelNotFoundException
     */
    public function index(Request $request, User $user)
    {
        $this->ensureCanAccessUser($request, $user);

        $perPage = max(1, min((int) $request->query('per_page', 50), 100));
        $notifications = $user->notifications()->paginate($perPage);

        return NotificationResource::collection($notifications);
    }

    /**
     * Show a specific notification of a user.
     *
     * @param  Request  $request
     * @param  User  $user
     * @param  ModelNotification  $notification
     * @return NotificationResource
     * 
     * @throws ModelNotFoundException
     */
    public function view(Request $request, User $user, ModelNotification $notification)
    {
        $this->ensureCanAccessUser($request, $user);
        $this->ensureNotificationBelongsToUser($user, $notification);

        return NotificationResource::make($notification);
    }

    /**
     * Send a notification to specific users.
     *
     * @param  SendToUsersNotificationRequest  $request
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function sendToUsers(SendToUsersNotificationRequest $request)
    {
        try {
            $data = $request->validated();
            $this->ensureTargetsOnlyTokenOwner($request, $data['users']);
            
            $via = match($data['via']) {
                'mail' => ['mail'],
                'database' => ['database'],
                'both' => ['mail', 'database'],
            };
            
            $database = in_array('database', $via) ? [
                'title' => $data['title'],
                'content' => strip_tags($data['content']),
            ] : null;
            
            $mail = in_array('mail', $via) ? 
                (new MailMessage)
                    ->subject($data['title'])
                    ->line(strip_tags($data['content']))
                : null;
            
            $users = $this->getTargetUsers($data);
            
            $this->notificationService->sendToUsers($users, $via, $database, $mail);

            return response()->json([
                'message' => 'Notification sent successfully.',
                'meta' => [
                    'user_count' => $users->count(),
                    'channels' => $via
                ]
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error' => 'Failed to send notification.',
                'message' => __('Failed to send notification.')
            ], 500);
        }
    }

    /**
     * Send a notification to all users.
     *
     * @param  SendToAllUsersNotificationRequest  $request
     * @return JsonResponse
     */
    public function sendToAll(SendToAllUsersNotificationRequest $request)
    {
        try {
            $this->ensureGlobalToken($request);

            $data = $request->validated();
            
            $via = match($data['via']) {
                'mail' => ['mail'],
                'database' => ['database'],
                'both' => ['mail', 'database'],
            };
            
            $database = in_array('database', $via) ? [
                'title' => $data['title'],
                'content' => strip_tags($data['content']),
            ] : null;
            
            $mail = in_array('mail', $via) ? 
                (new MailMessage)
                    ->subject($data['title'])
                    ->line(strip_tags($data['content']))
                : null;

            $userCount = User::query()->count();
            User::query()
                ->chunkById(500, function ($users) use ($via, $database, $mail) {
                    $this->notificationService->sendToUsers($users, $via, $database, $mail);
                });

            return response()->json([
                'message' => 'Notification sent successfully.',
                'meta' => [
                    'user_count' => $userCount,
                    'channels' => $via
                ]
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error' => 'Failed to send notification.',
                'message' => __('Failed to send notification.')
            ], 500);
        }
    }

    /**
     * Delete all notifications from an user.
     *
     * @param  Request  $request
     * @param  User  $user
     * @return JsonResponse
     * 
     * @throws ModelNotFoundException
     */
    public function destroyAll(Request $request, User $user)
    {
        $this->ensureCanAccessUser($request, $user);

        $count = $user->notifications()->delete();

        return response()->json([
            'message' => 'All notifications deleted successfully',
            'meta' => [
                'deleted_count' => $count
            ]
        ], 200);
    }

    /**
     * Delete a specific notification from an user.
     *
     * @param Request $request
     * @param  User  $user
     * @param  ModelNotification  $notification
     * @return \Illuminate\Http\Response
     * 
     * @throws ModelNotFoundException
     */
    public function destroyOne(Request $request, User $user, ModelNotification $notification)
    {
        $this->ensureCanAccessUser($request, $user);
        $this->ensureNotificationBelongsToUser($user, $notification);

        $notification->delete();

        return response()->noContent();
    }

    /**
     * Get target users based on the request data.
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Collection
     * 
     * @throws ValidationException
     */
    private function getTargetUsers(array $data): \Illuminate\Database\Eloquent\Collection
    {        
        $users = User::query()->whereIn('id', $data['users'])->get();

        if ($users->isEmpty()) {
            throw ValidationException::withMessages([
                'users' => ['No users found with the provided IDs.'],
            ]);
        }

        return $users;
    }

    private function ensureNotificationBelongsToUser(User $user, ModelNotification $notification): void
    {
        if (
            $notification->notifiable_type !== $user->getMorphClass()
            || (string) $notification->notifiable_id !== (string) $user->getKey()
        ) {
            abort(404);
        }
    }
}
