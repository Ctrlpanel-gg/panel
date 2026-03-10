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

        // determine which servers to unsuspend and deduct credits in a tight transaction
        $serversToUnsuspend = [];

        $event->user->getConnection()->transaction(function () use ($event, &$serversToUnsuspend) {
            // reload user row with a FOR UPDATE lock to prevent concurrent modifications
            $user = $event->user->newQuery()->lockForUpdate()->find($event->user->id);
            $userCredits = $user->credits;

            // fetch and sort suspended servers by price for deterministic behaviour
            $suspendedServers = $user->servers()
                ->with('product')
                ->whereNotNull('suspended')
                ->get()
                ->sortBy(fn (Server $s) => $s->product->price);

            foreach ($suspendedServers as $server) {
                if ($server->product->price > $userCredits) {
                    continue;
                }

                // reserve this server for unsuspension after the transaction
                $serversToUnsuspend[] = $server;
                $user->decrement('credits', $server->product->price);
                $userCredits -= $server->product->price;
            }
        });

        // communicate with panel after transaction completes
        foreach ($serversToUnsuspend as $server) {
            $unsuspendedServers[] = $server->unSuspend();
        }

        // ensure the original user model reflects the latest credits before notifying
        $event->user->refresh();

        if (!empty($unsuspendedServers)) {
            $event->user->notify(new ServersUnsuspendedNotification($unsuspendedServers));
        }
    }
}
