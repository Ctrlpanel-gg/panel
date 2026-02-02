<?php

namespace App\Listeners;

use App\Notifications\ServersUnsuspendedNotification;
use App\Events\UserUpdateCreditsEvent;
use App\Models\Server;

use Illuminate\Contracts\Queue\ShouldQueue;
use Exception;

class UnsuspendServers implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        // No global minimum credits setting anymore; decisions are made using product price.
    }

    /**
     * Handle the event.
     *
     * @param  UserUpdateCreditsEvent  $event
     * @return void
     *
     * @throws Exception
     */
    public function handle(UserUpdateCreditsEvent $event)
    {
        $unsuspendedServers = [];
        $userCredits = $event->user->credits;

        $suspendedServers = $event->user->servers()->with('product')->whereNotNull('suspended')->get();

        foreach ($suspendedServers as $server) {
            if ($server->product->price > $userCredits) {
                continue;
            }

            $unsuspendedServers[] = $server->unSuspend();
            $event->user->decrement('credits', $server->product->price);
        }

        if (!empty($unsuspendedServers)) {
            $event->user->notify(new ServersUnsuspendedNotification($unsuspendedServers));
        }
    }
}
