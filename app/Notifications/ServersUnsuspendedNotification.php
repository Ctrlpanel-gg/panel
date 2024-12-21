<?php

namespace App\Notifications;

use App\Settings\PterodactylSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServersUnsuspendedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $pterodactylSettings;
    protected $servers;

    /**
     * Create a new notification instance.
     */
    public function __construct($servers)
    {
        $this->pterodactylSettings = app(PterodactylSettings::class);
        $this->servers = $servers;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject(__('Your servers have been unsuspended'))
                    ->markdown('mail.server.unsuspended', [
                        'servers' => $this->servers,
                        'pterodactylSettings' => $this->pterodactylSettings,
                    ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('Your servers have been unsuspended'),
            'content' => '
                <h5>'.__('Your servers have been unsuspended').'</h5>
                <p>'.__('We appreciate your continued trust in our services. If you have any questions or need assistance, feel free to reach out to our support team.').'</p>
                <p>'.__('Regards').',<br />'.config('app.name', 'Laravel').'</p>
            '
        ];
    }
}
