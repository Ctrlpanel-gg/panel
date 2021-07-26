<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiscordUser;
use App\Models\User;
use App\Notifications\DynamicNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Displays all notifications of an user.
     * @param Request $request
     * @param int $userId
     * @return Response
     */
    public function index(Request $request, int $userId)
    {
        $discordUser = DiscordUser::find($userId);
        $user = $discordUser ? $discordUser->user : User::findOrFail($userId);

        return $user->notifications()->paginate($request->query("per_page", 50));
    }

    /**
     * Displays a specific notification
     * 
     * @param int $userId
     * @param int $notificationId
     * @return JsonResponse
     */
    public function view(int $userId, $notificationId)
    {
        $discordUser = DiscordUser::find($userId);
        $user = $discordUser ? $discordUser->user : User::findOrFail($userId);

        $notification = $user->notifications()->where("id", $notificationId)->get()->first();

        if (!$notification) {
            return response()->json(["message" => "Notification not found."], 404);
        }

        return $notification;
    }
    /**
     * Shows all unread notifications of an user.
     * @param Request $request
     * @param int $userId
     * @return Response
     */
    public function indexUnread(Request $request, int $userId)
    {
        $discordUser = DiscordUser::find($userId);
        $user = $discordUser ? $discordUser->user : User::findOrFail($userId);

        return $user->unreadNotifications()->paginate($request->query("per_page", 50));
    }

    /**
     * Send a notification to an user.
     * 
     * @param Request $request
     * @param int $userId
     * @return JsonResponse
     */
    public function send(Request $request, int $userId)
    {
        $discordUser = DiscordUser::find($userId);
        $user = $discordUser ? $discordUser->user : User::findOrFail($userId);

        $body = $request->validate([
            "title" => "required:string|min:0",
            "content" => "required:string|min:0"
        ]);

        $user->notify(
            new DynamicNotification($body["title"], $body["content"])
        );

        return response()->json(["message" => "Notification successfully sent."]);
    }

    /**
     * Delete all notifications from an user
     * 
     * @param int $userId
     * @return JsonResponse
     */
    public function delete(int $userId)
    {
        $discordUser = DiscordUser::find($userId);
        $user = $discordUser ? $discordUser->user : User::findOrFail($userId);

        $count = $user->notifications()->delete();

        return response()->json(["message" => "All notifications have been successfully deleted.", "count" => $count]);
    }

    /**
     * Delete all read notifications from an user
     * 
     * @param int $userId
     * @return JsonResponse
     */
    public function deleteRead(int $userId)
    {
        $discordUser = DiscordUser::find($userId);
        $user = $discordUser ? $discordUser->user : User::findOrFail($userId);

        $count = $user->notifications()->whereNotNull("read_at")->delete();

        return response()->json(["message" => "All read notifications have been successfully deleted.", "count" => $count]);
    }

    /**
     * Deletes a specific notification
     * 
     * @param int $userId
     * @param int $notificationId
     * @return JsonResponse
     */
    public function deleteOne(int $userId, $notificationid)
    {
        $discordUser = DiscordUser::find($userId);
        $user = $discordUser ? $discordUser->user : User::findOrFail($userId);

        $notification = $user->notifications()->where("id", $notificationid)->get()->first();

        if (!$notification) {
            return response()->json(["message" => "Notification not found."], 404);
        }

        $notification->delete();
        return response()->json($notification);
    }
}
