<?php

namespace App\Http\Controllers\Api;

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
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

/**
 * @group Notifications
 */

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService)
    {}

    /**
     * List user notifications
     *
     * @response {
     *  "data": [
     *    {
     *      "id": "2491fec3-c7d7-47cd-9d82-cc76e851c124",
     *      "type": "App\\Notifications\\ServerCreated",
     *      "details": {
     *        "title": "Server Created",
     *        "content": "Your server has been created successfully."
     *      },
     *      "read_at": null,
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *    }
     *  ],
     *  "meta": { "total": 1 }
     * }
     *
     * @param  Request  $request
     * @param  User  $user
     * @return NotificationResource
     *
     * @throws ModelNotFoundException
     */
    public function index(Request $request, User $user)
    {
        $notifications = $user->notifications()->paginate($request->query('per_page', 50));

        return NotificationResource::collection($notifications);
    }

    /**
     * Get notification details
     *
     * @urlParam user_id integer required The ID of the user. Example: 1
     *
     * @response {
     *  "data": {
     *      "id": "2491fec3-c7d7-47cd-9d82-cc76e851c124",
     *      "type": "App\\Notifications\\ServerCreated",
     *      "details": {
     *        "title": "Server Created",
     *        "content": "Your server has been created successfully."
     *      },
     *      "read_at": null,
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *  }
     * }
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
        return NotificationResource::make($notification);
    }

    /**
     * Send notification to users
     *
     * @response {
     *  "message": "Notification sent successfully.",
     *  "meta": {
     *    "user_count": 5,
     *    "channels": ["mail", "database"]
     *  }
     * }
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

            return response()->json([
                'message' => 'Notification sent successfully.',
                'meta' => [
                    'user_count' => $users->count(),
                    'channels' => $via
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to send notification.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send notification to everyone
     *
     * @response {
     *  "message": "Notification sent successfully.",
     *  "meta": {
     *    "user_count": 100,
     *    "channels": ["mail", "database"]
     *  }
     * }
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

            return response()->json([
                'message' => 'Notification sent successfully.',
                'meta' => [
                    'user_count' => $users->count(),
                    'channels' => $via
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to send notification.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all user notifications
     *
     * @response {
     *  "message": "All notifications deleted successfully",
     *  "meta": {
     *    "deleted_count": 10
     *  }
     * }
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

        return response()->json([
            'message' => 'All notifications deleted successfully',
            'meta' => [
                'deleted_count' => $count
            ]
        ], 200);
    }

    /**
     * Delete single notification
     *
     * @urlParam user_id integer required The ID of the user. Example: 1
     *
     * @response 204 {}
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
}
