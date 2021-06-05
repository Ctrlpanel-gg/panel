<?php

namespace App\Listeners;

use App\Models\Configuration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class Verified
{
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
        $event->user->increment('server_limit' , Configuration::getValueByKey('SERVER_LIMIT_REWARD_AFTER_VERIFY_EMAIL'));
        $event->user->increment('credits' , Configuration::getValueByKey('CREDITS_REWARD_AFTER_VERIFY_EMAIL'));
    }
}
