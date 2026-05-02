<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiErrorCode;
use App\Http\Resources\NotificationResource;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\Notification as ModelNotification;
use App\Http\Requests\Api\Notifications\SendToAllUsersNotificationRequest;
use App\Http\Requests\Api\Notifications\SendToUsersNotificationRequest;
use App\Services\ApiResponseService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;

class NotificationController extends Controller
{
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
        $perPage = min((int) $request->query('per_page', 50), 100);
        $notifications = $user->notifications()->paginate($perPage);

        return ApiResponseService::success(
            NotificationResource::collection($notifications)->toArray($request),
            [
                'current_page' => $notifications->currentPage(),
                'total' => $notifications->total(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'from' => $notifications->firstItem(),
                'to' => $notifications->lastItem(),
            ]
        );
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
        return ApiResponseService::success(NotificationResource::make($notification)->toArray($request));
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

            $via = match($data['via']) {
                'mail' => ['mail'],
                'database' => ['database'],
                'both' => ['mail', 'database'],
            };

            $database = in_array('database', $via) ? [
                'title' => $data['title'],
                'content' => $data['content'],
            ] : null;

            $mail = in_array('mail', $via) ?
                (new MailMessage)
                    ->subject($data['title'])
                    ->line(new HtmlString($data['content']))
                : null;

            $users = $this->getTargetUsers($data);

            $this->notificationService->sendToUsers($users, $via, $database, $mail);

            return ApiResponseService::success(
                ['message' => 'Notification sent successfully.'],
                [
                    'user_count' => $users->count(),
                    'channels' => $via
                ]
            );
        } catch (Exception $e) {
            return ApiResponseService::error(
                ApiErrorCode::INTERNAL_ERROR,
                'Failed to send notification: ' . $e->getMessage(),
                500
            );
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
            $data = $request->validated();

            $via = match($data['via']) {
                'mail' => ['mail'],
                'database' => ['database'],
                'both' => ['mail', 'database'],
            };

            $database = in_array('database', $via) ? [
                'title' => $data['title'],
                'content' => $data['content'],
            ] : null;

            $mail = in_array('mail', $via) ?
                (new MailMessage)
                    ->subject($data['title'])
                    ->line(new HtmlString($data['content']))
                : null;

            $users = User::all();

            $this->notificationService->sendToUsers($users, $via, $database, $mail);

            return ApiResponseService::success(
                ['message' => 'Notification sent successfully.'],
                [
                    'user_count' => $users->count(),
                    'channels' => $via
                ]
            );
        } catch (Exception $e) {
            return ApiResponseService::error(
                ApiErrorCode::INTERNAL_ERROR,
                'Failed to send notification: ' . $e->getMessage(),
                500
            );
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
        $count = $user->notifications()->delete();

        return ApiResponseService::success(
            ['message' => 'All notifications deleted successfully'],
            ['deleted_count' => $count]
        );
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
        $notification->delete();

        return ApiResponseService::noContent();
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
}
