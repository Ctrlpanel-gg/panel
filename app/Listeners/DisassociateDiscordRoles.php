<?php

namespace App\Listeners;


use App\Events\ServerDeletedEvent;
use App\Settings\DiscordSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Exception;

class DisassociateDiscordRoles implements ShouldQueue
{
    protected DiscordSettings $discordSettings;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->discordSettings = app(DiscordSettings::class);
    }

    /**
     * Handle the event.
     */
    public function handle(ServerDeletedEvent $event): void
    {
        try {
            if ($this->discordSettings->role_for_active_clients && $event->server->user->discordUser && $event->server->user->servers->count() <= 1) {
                $event->server->user->discordUser->addOrRemoveRole('remove', $this->discordSettings->role_id_for_active_clients);
            }
        } catch (Exception $e) {
           Log::error('Discord role update failed', [
                'error' => $e->getMessage(),
                'user_id' => $event->server->user->id,
                'server_id' => $event->server->id,
            ]);
        }
    }
}
