<?php

namespace App\Listeners;

use App\Events\UserUpdateCreditsEvent;
use App\Models\Server;
use App\Models\Settings;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;

class UnsuspendServers implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param UserUpdateCreditsEvent $event
     * @return void
     * @throws Exception
     */
    public function handle(UserUpdateCreditsEvent $event)
    {
       if ($event->user->credits > Settings::getValueByKey('SETTINGS::USER:MINIMUM_REQUIRED_CREDITS_TO_MAKE_SERVER' , 50)){
           /** @var Server $server */
           foreach ($event->user->servers as $server){
               if ($server->isSuspended()) $server->unSuspend();
           }
       }
    }
}
