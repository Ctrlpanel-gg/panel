<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\DynamicNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Send notification to multiple users
     */
    public function sendToUsers(Collection $users, array $via, ?array $database = null, ?MailMessage $mail = null): void
    {
        Notification::send($users, new DynamicNotification($via, $database, $mail));
    }

    /**
     * Send notification to single user
     */
    public function sendToUser(User $user, array $via, ?array $database = null, ?MailMessage $mail = null): void
    {
        $user->notify(new DynamicNotification($via, $database, $mail));
    }

    /**
     * Send database notification only
     */
    public function sendDatabaseNotification(User $user, string $title, string $content): void
    {
        $this->sendToUser($user, ['database'], ['title' => $title, 'content' => $content]);
    }

    /**
     * Send email notification only
     */
    public function sendEmailNotification(User $user, string $subject, string $content): void
    {
        $mail = (new MailMessage)
            ->subject($subject)
            ->line($content);

        $this->sendToUser($user, ['mail'], null, $mail);
    }
}