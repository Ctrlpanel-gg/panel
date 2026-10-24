<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class DiscordJoinFailed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var User
     */
    private $user;

    /**
     * Create a new notification instance.
     *
     * @param  User  $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $userName = e($this->user->name);
        if ($notifiable->is($this->user)) {
            $content = "
                <p>Hello <strong>{$userName}</strong>, we were unable to add you to our Discord server.</p>
                <p>Please try linking your Discord account again, or contact our support team for assistance.</p>
            ";
        } else {
            $content = "
                <p>Discord server join failed for <strong>{$userName}</strong>.</p>
                <p>Please investigate the Discord integration or assist the user.</p>
            ";
        }

        return [
            'title' => __('Discord Server Join Failed'),
            'content' => $content . '<p>' . config('app.name', 'CtrlPanel.gg') . '</p>',
        ];
    }
}
