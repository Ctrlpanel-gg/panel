<?php

namespace App\Jobs;

use App\Classes\PterodactylClient;
use App\Models\Server;
use App\Services\CreditService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReconcileServerCreationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public string $serverId;
    public float $chargedPrice;

    public function __construct(string $serverId, float $chargedPrice)
    {
        $this->serverId = $serverId;
        $this->chargedPrice = $chargedPrice;
        $this->queue = 'default';
    }

    public function handle(PterodactylClient $pterodactylClient, CreditService $creditService): void
    {
        $server = Server::find($this->serverId);

        if (!$server) {
            return;
        }

        if ($server->status === Server::STATUS_FAILED) {
            return;
        }

        if ($server->status === Server::STATUS_ACTIVE && $server->pterodactyl_id) {
            return;
        }

        if ($server->pterodactyl_id) {
            $server->update(['status' => Server::STATUS_ACTIVE]);
            dispatch(new PostServerCreationJob($server->id));
            return;
        }

        try {
            $response = $pterodactylClient->getServerByExternalId($server->id);
        } catch (\Exception $e) {
            Log::warning('ReconcileServerCreationJob: pterodactyl API request failed', [
                'server_id' => $server->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        if ($response->successful()) {
            $attributes = $response->json()['attributes'] ?? null;

            if (!$attributes || !isset($attributes['id']) || !isset($attributes['identifier'])) {
                throw new \Exception('ReconcileServerCreationJob: invalid pterodactyl server payload');
            }

            $server->update([
                'pterodactyl_id' => $attributes['id'],
                'identifier' => $attributes['identifier'],
                'status' => Server::STATUS_ACTIVE,
            ]);

            dispatch(new PostServerCreationJob($server->id));

            return;
        }

        if ($response->status() === 404) {
            // Avoid double refunds if already failed or already handled by another instance.
            if ($server->status !== Server::STATUS_FAILED) {
                $creditService->refund($server->user, $this->chargedPrice);
                $server->update(['status' => Server::STATUS_FAILED]);
            }
            return;
        }

        throw new \Exception('ReconcileServerCreationJob: pterodactyl API returned non-404 failure status ' . $response->status());
    }

    public function failed(\Throwable $exception): void
    {
        $server = Server::find($this->serverId);

        if (!$server || $server->status === Server::STATUS_ACTIVE) {
            return;
        }

        $server->update(['status' => Server::STATUS_FAILED]);

        Log::critical('ReconcileServerCreationJob failed after maximum retries', [
            'server_id' => $this->serverId,
            'error' => $exception->getMessage(),
        ]);
    }
}
