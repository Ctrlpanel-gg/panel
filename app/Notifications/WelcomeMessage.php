<?php

namespace App\Notifications;

use App\Models\User;
use App\Settings\GeneralSettings;
use App\Settings\UserSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WelcomeMessage extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var User
     */
    private $user;

    private $credits_display_name;

    private $credits_reward_after_verify_discord;

    private $credits_reward_after_verify_email;

    private $server_limit_increment_after_verify_discord;

    private $server_limit_increment_after_verify_email;

    /**
     * Create a new notification instance.
     *
     * @param  User  $user
     */
    public function __construct(User $user)
    {
        $general_settings= new GeneralSettings();
        $user_settings = new UserSettings();

        $this->user = $user;
        $this->credits_display_name = $general_settings->credits_display_name;
        $this->credits_reward_after_verify_discord = $user_settings->credits_reward_after_verify_discord;
        $this->credits_reward_after_verify_email = $user_settings->credits_reward_after_verify_email;
        $this->server_limit_increment_after_verify_discord = $user_settings->server_limit_increment_after_verify_discord;
        $this->server_limit_increment_after_verify_email = $user_settings->server_limit_increment_after_verify_email;
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

    public function AdditionalLines()
    {
        $AdditionalLine = '';
        if ($this->credits_reward_after_verify_email != 0) {
            $AdditionalLine .= __('Verifying your e-mail address will grant you ').$this->credits_reward_after_verify_email.' '.__('additional').' '.$this->credits_display_name.'. <br />';
        }
        if ($this->server_limit_increment_after_verify_email != 0) {
            $AdditionalLine .= __('Verifying your e-mail will also increase your Server Limit by ').$this->server_limit_increment_after_verify_email.'. <br />';
        }
        $AdditionalLine .= '<br />';
        if ($this->credits_reward_after_verify_discord != 0) {
            $AdditionalLine .= __('You can also verify your discord account to get another ').$this->credits_reward_after_verify_discord.' '.$this->credits_display_name.'. <br />';
        }
        if ($this->server_limit_increment_after_verify_discord != 0) {
            $AdditionalLine .= __('Verifying your Discord account will also increase your Server Limit by ').$this->server_limit_increment_after_verify_discord.'. <br />';
        }

        return $AdditionalLine;
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
            'title' => __('Getting started!'),
            'content' => '
               <p> '.__('Hello')." <strong>{$this->user->name}</strong>, ".__('Welcome to our dashboard').'!</p>
                <h5>'.__('Verification').'</h5>
                <p>'.__('You can verify your e-mail address and link/verify your Discord account.').'</p>
                <p>
                  '.$this->AdditionalLines().'
                </p>
                <h5>'.__('Information').'</h5>
                <p>'.__('This dashboard can be used to create and delete servers').'.<br /> '.__('These servers can be used and managed on our pterodactyl panel').'.<br /> '.__('If you have any questions, please join our Discord server and #create-a-ticket').'.</p>
                <p>'.__('We hope you can enjoy this hosting experience and if you have any suggestions please let us know').'!</p>
                <p>'.__('Regards').',<br />'.config('app.name', 'Laravel').'</p>
            ',
        ];
    }
}
