<?php

namespace App\Listeners;

use App\Notifications\ServersUnsuspendedNotification;
use App\Events\UserUpdateCreditsEvent;
use App\Models\Server;
use App\Settings\UserSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Exception;

class UnsuspendServers implements ShouldQueue
{
    private $min_credits_to_make_server;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(UserSettings $user_settings)
    {
        $this->min_credits_to_make_server = $user_settings->min_credits_to_make_server;
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

        if ($event->user->credits >= $this->min_credits_to_make_server) {
            $suspendedServers = $event->user->servers()->with('product')->whereNotNull('suspended')->get();

            foreach ($suspendedServers as $server) {
                if ($server->product->price > $event->user->credits) {
                    continue;
                }

                $unsuspendedServers[] = $server->unSuspend();
                $event->user->decrement('credits', $server->product->price);
            }
        }

        if (!empty($unsuspendedServers)) {
            $event->user->notify(new ServersUnsuspendedNotification($unsuspendedServers));
        }
    }
}
