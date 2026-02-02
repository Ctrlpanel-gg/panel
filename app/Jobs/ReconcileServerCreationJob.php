<?php

namespace App\Jobs;

use App\Classes\PterodactylClient;
use App\Settings\PterodactylSettings;
use App\Jobs\HandlePostServerCreationJob;
use App\Models\Server;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ReconcileServerCreationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    private string $serverId;
    private float $price;

    /**
     * @param string $serverId
     * @param float $price
     */
    public function __construct(string $serverId, float $price)
    {
        $this->serverId = $serverId;
        $this->price = $price;
    }

    public function handle(): void
    {
        $server = Server::find($this->serverId);
        if (!$server) {
            return; // Nothing to do
        }

        // If server already has a pterodactyl_id, assume success and ensure post-creation tasks run
        if ($server->pterodactyl_id) {
            HandlePostServerCreationJob::dispatch($server->user_id, $server->id);

            // Clear cached credits
            Cache::forget('user_credits_left:' . $server->user_id);
            return;
        }

        $pteroClient = new PterodactylClient(new PterodactylSettings());

        try {
            $attrs = $pteroClient->findServerByExternalId($server->id);
        } catch (Exception $e) {
            Log::error('Reconcile job: failed to query Pterodactyl: ' . $e->getMessage(), ['server_id' => $server->id]);
            throw $e; // Let the job retry
        }

        if ($attrs) {
            // Remote server exists — update local record and dispatch post-creation tasks
            $server->update([
                'pterodactyl_id' => $attrs['id'],
                'identifier' => $attrs['identifier'] ?? $server->identifier,
            ]);

            HandlePostServerCreationJob::dispatch($server->user_id, $server->id);
            Cache::forget('user_credits_left:' . $server->user_id);
            return;
        }

        // Remote server does not exist — attempt refund and delete local record
        try {
            User::where('id', $server->user_id)->increment('credits', $this->price);

            // Clear cache and delete server locally (deleting hook will ignore 404s on pterodactyl)
            Cache::forget('user_credits_left:' . $server->user_id);
            $server->delete();
        } catch (Exception $e) {
            Log::error('Reconcile job: failed to refund or delete server', ['server_id' => $server->id, 'error' => $e->getMessage()]);
            throw $e; // retry
        }
    }
}
