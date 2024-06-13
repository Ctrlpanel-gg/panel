<?php

namespace App\Listeners;

class Verified
{
    private $server_limit_after_verify_email;
    private $credits_reward_after_verify_email;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if (!$event->user->email_verified_reward) {
            $event->user->increment('server_limit', $this->server_limit_after_verify_email);
            $event->user->increment('credits', $this->credits_reward_after_verify_email);
            $event->user->update(['email_verified_reward' => true]);
        }
    }
}
