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
    public int $chargedPrice;

    public function __construct(string $serverId, int $chargedPrice)
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
            $user = $server->user;

            // Atomic delete: only the worker that actually removes the row refunds.
            $deleted = Server::where('id', $server->id)
                ->where('status', '!=', Server::STATUS_FAILED)
                ->delete();

            if ($deleted === 1) {
                if ($user) {
                    $creditService->refund($user, $this->chargedPrice);
                    Log::info('ReconcileServerCreationJob: deleted server and refunded credits on confirmed 404', [
                        'server_id' => $this->serverId,
                        'amount' => $this->chargedPrice,
                    ]);
                } else {
                    Log::info('ReconcileServerCreationJob: deleted server with no owner, nothing to refund', [
                        'server_id' => $this->serverId,
                    ]);
                }
            } else {
                Log::info('ReconcileServerCreationJob: server already handled by another worker, skipping refund', [
                    'server_id' => $this->serverId,
                ]);
            }

            return;
        }

        throw new \Exception('ReconcileServerCreationJob: pterodactyl API returned non-404 failure status ' . $response->status());
    }

    public function failed(\Throwable $exception): void
    {
        $server = Server::find($this->serverId);

        if (!$server || $server->status === Server::STATUS_ACTIVE || $server->status === Server::STATUS_FAILED) {
            return;
        }

        $pterodactylClient = app(PterodactylClient::class);
        $creditService = app(CreditService::class);

        try {
            $response = $pterodactylClient->getServerByExternalId($server->id);
        } catch (\Exception $e) {
            $server->update(['status' => Server::STATUS_PENDING_RECONCILIATION]);
            Log::critical('ReconcileServerCreationJob failed and final remote check failed; keeping pending_reconciliation', [
                'server_id' => $this->serverId,
                'exception' => $e->getMessage(),
            ]);

            return;
        }

        if ($response->successful()) {
            $attributes = $response->json()['attributes'] ?? null;
            if ($attributes && isset($attributes['id']) && isset($attributes['identifier'])) {
                $server->update([
                    'pterodactyl_id' => $attributes['id'],
                    'identifier' => $attributes['identifier'],
                    'status' => Server::STATUS_ACTIVE,
                ]);
                dispatch(new PostServerCreationJob($server->id));

                Log::critical('ReconcileServerCreationJob exhausted retries but remote server finally found; marked active', [
                    'server_id' => $this->serverId,
                ]);

                return;
            }
        }

        if ($response->status() === 404) {
            $user = $server->user;

            // Atomic delete: only the worker that actually removes the row refunds.
            $deleted = Server::where('id', $server->id)
                ->where('status', '!=', Server::STATUS_FAILED)
                ->delete();

            if ($deleted === 1) {
                if ($user) {
                    $creditService->refund($user, $this->chargedPrice);
                }

                Log::critical('ReconcileServerCreationJob exhausted retries with remote 404; auto-deleted', [
                    'server_id' => $this->serverId,
                    'amount' => $this->chargedPrice,
                ]);
            } else {
                Log::info('ReconcileServerCreationJob: server already handled by another worker, skipping refund', [
                    'server_id' => $this->serverId,
                ]);
            }

            return;
        }

        // Unknown remote state (persistent 500/timeout): never refund blind, keep pending for admin.
        $server->update(['status' => Server::STATUS_PENDING_RECONCILIATION]);
        Log::critical('ReconcileServerCreationJob exhausted retries and remote state unknown; keeping pending_reconciliation', [
            'server_id' => $this->serverId,
            'error' => $exception->getMessage(),
        ]);
    }
}
