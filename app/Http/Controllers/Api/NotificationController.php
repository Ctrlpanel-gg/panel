<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiscordUser;
use App\Models\User;
use App\Notifications\DynamicNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;
use Spatie\ValidationRules\Rules\Delimited;

class NotificationController extends Controller
{
    /**
     * Display all notifications of an user.
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
     * Display a specific notification
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

        $via = $request->validate([
            "via" => ["required", new Delimited("in:mail,database")]
        ]);
        $via = explode(',', $via["via"]);
        $mail = null;
        $database = null;
        if (in_array('database', $via)) {
            $database = $request->validate([
                "title" => "required_if:database,true|string|min:1",
                "content" => "required_if:database,true|string|min:1"
            ]);
        }
        if (in_array('mail', $via)) {
            $data = $request->validate([
                "subject" => "required|string|min:1",
                "body" => "required|string|min:1"
            ]);
            $mail = (new MailMessage)->subject($data["subject"])->line(new HtmlString($data["body"]));
        }
        $user->notify(
            new DynamicNotification($via, $database, $mail)
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
     * Delete a specific notification
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
