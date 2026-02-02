<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use App\Settings\MailSettings;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class ServerSuspensionWarningNotification extends Notification
{
    protected $servers;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($servers)
    {
        $this->servers = $servers;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = ['database'];

        $mailSettings = app(MailSettings::class);

        if ($mailSettings->mail_from_address && $mailSettings->mail_host) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $sortedServers = $this->getSortedServersByPriority();

        $totalCreditsNeeded = $sortedServers->sum(function ($serverData) {
            return $serverData['server']->product->price;
        });
        $serverList = $sortedServers->map(function ($serverData, $index) {
            $server = $serverData['server'];
            return '• ' . $server->name . ' (will be suspended on ' . $serverData['suspension_date']->format('M j, Y \a\t g:i A') . ')';
        })->implode("\n");

        $currentCredits = app(CurrencyHelper::class)->formatForDisplay($notifiable->credits);
        $totalNeededDisplay = app(CurrencyHelper::class)->formatForDisplay($totalCreditsNeeded);
        $additionalNeeded = max(0, $totalCreditsNeeded - $notifiable->credits);
        $additionalNeededDisplay = app(CurrencyHelper::class)->formatForDisplay($additionalNeeded);

        return (new MailMessage)
                    ->subject('Server Suspension Warning - Action Required')
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('Your server(s) are scheduled for suspension due to insufficient credits:')
                    ->line($serverList)
                    ->line('Credit Status:')
                    ->line('• Current balance: ' . $currentCredits)
                    ->line('• Total needed for these servers: ' . $totalNeededDisplay)
                    ->line('• Additional credits needed: ' . $additionalNeededDisplay)
                    ->line('**Important:** Servers will be suspended in billing priority order (lowest priority first). If you don\'t have enough credits for all servers, lower priority servers will be suspended before higher priority ones.')
                    ->action('Add Credits Now', route('store.index'))
                    ->line('If you have any questions, please contact our support team.')
                    ->salutation('Best regards, ' . config('app.name', 'CtrlPanel') . ' Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        // Sort servers by billing priority (LOW first = suspended first, HIGH last = suspended last)
        $sortedServers = $this->getSortedServersByPriority();

        $totalCreditsNeeded = $sortedServers->sum(function ($serverData) {
            return $serverData['server']->product->price;
        });
        $serverList = $sortedServers->map(function ($serverData, $index) {
            $server = $serverData['server'];
            $priorityText = '';
            if ($index === 0) {
                $priorityText = ' <strong>(Lowest priority - suspended first)</strong>';
            } elseif ($index === $this->servers->count() - 1 && $this->servers->count() > 1) {
                $priorityText = ' <strong>(Highest priority - suspended last)</strong>';
            }
            return '<li>' . $server->name . ' (will be suspended on ' . $serverData['suspension_date']->format('M j, Y \a\t g:i A') . ')' . $priorityText . '</li>';
        })->implode('');

        $currentCredits = app(CurrencyHelper::class)->formatForDisplay($notifiable->credits);
        $totalNeededDisplay = app(CurrencyHelper::class)->formatForDisplay($totalCreditsNeeded);
        $additionalNeeded = max(0, $totalCreditsNeeded - $notifiable->credits);
        $additionalNeededDisplay = app(CurrencyHelper::class)->formatForDisplay($additionalNeeded);

        return [
            'title' => 'Server Suspension Warning - Action Required',
            'content' => '
                <p><strong>Hello ' . $notifiable->name . ',</strong></p>
                <p>Your server(s) are scheduled for suspension due to insufficient credits:</p>
                <ul>' . $serverList . '</ul>
                <div style="border: 1px solid #dee2e6; padding: 12px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #ffc107;">
                    <strong>Credit Status:</strong><br>
                    • Current balance: ' . $currentCredits . '<br>
                    • Total needed for these servers: ' . $totalNeededDisplay . '<br>
                    • Additional credits needed: <strong>' . $additionalNeededDisplay . '</strong>
                </div>
                <p><strong>Important:</strong> Servers will be suspended in billing priority order (lowest priority first). If you don\'t have enough credits for all servers, lower priority servers will be suspended before higher priority ones.</p>
                <p><a href="' . route('store.index') . '" style="background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;">Add Credits Now</a></p>
                <p>If you have any questions, please contact our support team.</p>
                <p><em>Best regards,<br />' . config('app.name', 'CtrlPanel') . '</em></p>
            ',
        ];
    }

    /**
     * Get servers sorted by billing priority.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getSortedServersByPriority()
    {
        return $this->servers->sortBy(function ($serverData) {
            $server = $serverData['server'];
            return $server->billing_priority ?? $server->product->default_billing_priority;
        });
    }
}
