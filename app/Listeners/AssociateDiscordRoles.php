<?php

namespace App\Listeners;

use App\Events\ServerCreatedEvent;
use App\Settings\DiscordSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Exception;

class AssociateDiscordRoles implements ShouldQueue
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
    public function handle(ServerCreatedEvent $event): void
    {
        try {
            if ($this->discordSettings->role_for_active_clients && $event->user->discordUser && $event->user->servers->count() > 0) {
                $event->user->discordUser->addOrRemoveRole('add', $this->discordSettings->role_id_for_active_clients);
            }
        } catch (Exception $e) {
            Log::error('Discord role update failed', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id,
                'server_id' => $event->server->id,
            ]);
        }
    }
}
