<?php

namespace App\Notifications;

use App\Models\Settings;
use App\Models\User;
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
    public function AdditionalLines()
        {

            $AdditionalLine = "";
            if(Settings::getValueByKey('SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_EMAIL') != 0) {
                $AdditionalLine .= "Verifying your e-mail address will grant you ".Settings::getValueByKey('SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_EMAIL')." additional " . Settings::getValueByKey('SETTINGS::SYSTEM:CREDITS_DISPLAY_NAME') . ". <br />";
            }
            if(Settings::getValueByKey('SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_EMAIL') != 0) {
                $AdditionalLine .= "Verifying your e-mail will also increase your Server Limit by " . Settings::getValueByKey('SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_EMAIL') . ". <br />";
            }
            $AdditionalLine .="<br />";
            if(Settings::getValueByKey('SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_DISCORD') != 0) {
                $AdditionalLine .=  "You can also verify your discord account to get another " . Settings::getValueByKey('SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_DISCORD') . " " . Settings::getValueByKey('SETTINGS::SYSTEM:CREDITS_DISPLAY_NAME') . ". <br />";
            }
            if(Settings::getValueByKey('SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_DISCORD') != 0) {
                $AdditionalLine .=  "Verifying your Discord account will also increase your Server Limit by " . Settings::getValueByKey('SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_DISCORD') . ". <br />";
            }

            return $AdditionalLine;
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
            'title'   => __("Getting started!"),
            'content' => "
               <p>Hello <strong>{$this->user->name}</strong>, Welcome to our dashboard!</p>
                <h5>Verification</h5>
                <p>You can verify your e-mail address and link/verify your Discord account.</p>
                <p>
                  ".$this->AdditionalLines()."
                </p>
                <h5>Information</h5>
                <p>This dashboard can be used to create and delete servers.<br /> These servers can be used and managed on our pterodactyl panel.<br /> If you have any questions, please join our Discord server and #create-a-ticket.</p>
                <p>We hope you can enjoy this hosting experience and if you have any suggestions please let us know!</p>
                <p>Regards,<br />" . config('app.name', 'Laravel') . "</p>
            ",
        ];
    }
}
