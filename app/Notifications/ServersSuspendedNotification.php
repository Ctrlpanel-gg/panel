<?php

namespace App\Notifications;

use App\Models\Configuration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServersSuspendedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail' , 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Your servers have been suspended!')
                    ->greeting('Your servers have been suspended!')
                    ->line("To automatically re-enable your server/s, you need to purchase more credits.")
                    ->action('Purchase credits', route('store.index'))
                    ->line('If you have any questions please let us know.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title'   => "Servers suspended!",
            'content' => "
                <h5>Your servers have been suspended!</h5>
                <p>To automatically re-enable your server/s, you need to purchase more credits.</p>
                <p>If you have any questions please let us know.</p>
                <p>Regards,<br />" . config('app.name', 'Laravel') . "</p>
            ",
        ];
    }
}
