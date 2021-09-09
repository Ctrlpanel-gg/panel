<?php

namespace App\Listeners;

use App\Events\UserUpdateCreditsEvent;
use App\Models\Configuration;
use App\Models\Server;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
       if ($event->user->credits > Configuration::getValueByKey('MINIMUM_REQUIRED_CREDITS_TO_MAKE_SERVER' , 50)){
           /** @var Server $server */
           foreach ($event->user->servers as $server){
               if ($server->isSuspended()) $server->unSuspend();
           }
       }
    }
}
