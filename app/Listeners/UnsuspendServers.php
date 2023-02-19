<?php

namespace App\Listeners;

use App\Events\UserUpdateCreditsEvent;
use App\Models\Server;
use App\Settings\UserSettings;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        if ($event->user->credits > $this->min_credits_to_make_server) {
            /** @var Server $server */
            foreach ($event->user->servers as $server) {
                if ($server->isSuspended()) {
                    $server->unSuspend();
                }
            }
        }
    }
}
