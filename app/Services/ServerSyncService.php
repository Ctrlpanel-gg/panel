<?php

namespace App\Services;

use App\Classes\PterodactylClient;
use App\Exceptions\Pterodactyl\PterodactylException;
use App\Exceptions\Pterodactyl\PterodactylNotFoundException;
use App\Models\Server;
use Illuminate\Support\Facades\Log;

/**
 * Reconciles local servers against Pterodactyl. Deletes only on confirmed 404, skips on any other failure.
 */
class ServerSyncService
{
    private PterodactylClient $pterodactyl;

    public function __construct(?PterodactylClient $pterodactyl = null)
    {
        $this->pterodactyl = $pterodactyl ?? app(PterodactylClient::class);
    }

    /**
     * @return array{renamed: int, deleted: int, skipped: int, errors: array<int, string>}
     *
     * @throws PterodactylException when the remote list itself cannot be fetched.
     */
    public function sync(): array
    {
        // Fail closed: if this throws, the caller aborts with zero deletes.
        $remoteById = [];
        foreach ($this->pterodactyl->getAllServers() as $server) {
            $id = $server['attributes']['id'] ?? null;
            if ($id !== null) {
                $remoteById[$id] = $server['attributes']['name'] ?? null;
            }
        }

        $result = [
            'renamed' => 0,
            'deleted' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        Server::query()->whereNotNull('pterodactyl_id')->chunkById(200, function ($servers) use ($remoteById, &$result) {
            /** @var Server $server */
            foreach ($servers as $server) {
                $pterodactylId = $server->pterodactyl_id;

                if (array_key_exists($pterodactylId, $remoteById)) {
                    $this->syncName($server, $remoteById[$pterodactylId], $result);
                    continue;
                }

                // Absent from list: confirm with a probe, delete only on 404.
                try {
                    $attributes = $this->pterodactyl->getServerAttributes($pterodactylId);
                    $this->syncName($server, $attributes['name'] ?? null, $result);
                } catch (PterodactylNotFoundException $e) {
                    // Query builder skips model events (no remote DELETE re-fire).
                    Server::query()->where('pterodactyl_id', $pterodactylId)->delete();
                    $result['deleted']++;

                    Log::info('Server sync: removed orphaned server', [
                        'server_id' => $server->id,
                        'pterodactyl_id' => $pterodactylId,
                    ]);
                } catch (PterodactylException $e) {
                    $result['skipped']++;
                    $result['errors'][] = "#{$pterodactylId}: {$e->getPublicMessage()}";

                    Log::warning('Server sync: skipped server after probe failure', [
                        'server_id' => $server->id,
                        'pterodactyl_id' => $pterodactylId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        return $result;
    }

    /**
     * @param  array{renamed: int, deleted: int, skipped: int, errors: array}  $result
     */
    private function syncName(Server $server, ?string $remoteName, array &$result): void
    {
        if ($remoteName !== null && $server->name !== $remoteName) {
            $server->name = $remoteName;
            $server->save();
            $result['renamed']++;
        }
    }
}
