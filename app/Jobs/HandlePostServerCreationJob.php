<?php

namespace App\Jobs;

use App\Models\Server;
use App\Models\User;
use App\Settings\DiscordSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class HandlePostServerCreationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;
    public string $serverId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, string $serverId)
    {
        $this->userId = $userId;
        $this->serverId = $serverId;
    }

    /**
     * Execute the job.
     */
    public function handle(DiscordSettings $discordSettings): void
    {
        $user = User::find($this->userId);
        $server = Server::find($this->serverId);

        if (! $user || ! $server) {
            Log::warning('Post server creation job: missing user or server', ['user' => $this->userId, 'server' => $this->serverId]);
            return;
        }


        // Discord role update (best-effort)
        try {
            if ($discordSettings->role_for_active_clients && $user->discordUser && $user->servers()->count() >= 1) {
                $user->discordUser->addOrRemoveRole('add', $discordSettings->role_id_for_active_clients);
            }
        } catch (Exception $e) {
            Log::debug('Discord role update failed in job: ' . $e->getMessage(), ['user' => $this->userId, 'server' => $this->serverId]);
        }
    }
}
