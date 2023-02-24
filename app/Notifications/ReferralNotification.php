<?php

namespace App\Notifications;

use App\Models\User;
use App\Settings\GeneralSettings;
use App\Settings\ReferralSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReferralNotification extends Notification
{
    use Queueable;

    /**
     * @var User
     */
    private $user;

    private $ref_user;

    /**
     * Create a new notification instance.
     *
     * @param  User  $user
     */
    public function __construct(int $user, int $ref_user)
    {
        $this->user = User::findOrFail($user);
        $this->ref_user = User::findOrFail($ref_user);
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
    public function toArray($notifiable, GeneralSettings $general_settings, ReferralSettings $referral_settings)
    {
        return [
            'title' => __('Someone registered using your Code!'),
            'content' => '
                <p>You received '. $referral_settings->reward . ' ' . $general_settings->credits_display_name . '</p>
                <p>because ' . $this->ref_user->name . ' registered with your Referral-Code!</p>
                <p>Thank you very much for supporting us!.</p>
                <p>'.config('app.name', 'Laravel').'</p>
            ',
        ];
    }
}
