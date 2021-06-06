<?php

namespace App\Notifications;

use App\Models\Configuration;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WelcomeMessage extends Notification
{
    use Queueable;

    /**
     * @var User
     */
    private $user;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title'   => "Getting started!",
            'content' => "
               <p>Hello <strong>{$this->user->name}</strong>, Welcome to our dashboard!</p>
                <h5>Verification</h5>
                <p>Please verify your email address to get " . Configuration::getValueByKey('CREDITS_REWARD_AFTER_VERIFY_EMAIL') . " extra credits and increase your server limit to " . Configuration::getValueByKey('SERVER_LIMIT_REWARD_AFTER_VERIFY_EMAIL') . "<br />You can also verify your discord account to get another " . Configuration::getValueByKey('CREDITS_REWARD_AFTER_VERIFY_DISCORD') . " credits and to increase your server limit again with " . Configuration::getValueByKey('SERVER_LIMIT_REWARD_AFTER_VERIFY_DISCORD') . "</p>
                <h5>Information</h5>
                <p>This dashboard can be used to create and delete servers.<br /> These servers can be used and managed on our pterodactyl panel.<br /> If you have any questions, please join our Discord server and #create-a-ticket.</p>
                <p>We hope you can enjoy this hosting experience and if you have any suggestions please let us know!</p>
                <p>Regards,<br />" . config('app.name', 'Laravel') . "</p>
            ",
        ];
    }
}
