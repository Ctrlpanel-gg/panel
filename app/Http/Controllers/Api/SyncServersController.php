<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Classes\PterodactylClient;
use App\Settings\PterodactylSettings;
use Illuminate\Http\Request;

class SyncServersController extends Controller
{
    protected $pterodactylClient;

    public function __construct(PterodactylSettings $pterodactylSettings)
    {
        $this->pterodactylClient = new PterodactylClient($pterodactylSettings);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $pterodactylServers = collect($this->pterodactylClient->getServers());

        $updatedServers = $pterodactylServers->map(function ($server) {
            $serverModel = Server::where('pterodactyl_id', $server['attributes']['id'])->first();

            if ($serverModel && $this->shouldUpdateServer($serverModel, $server)) {
                $serverModel->update([
                    'name' => $server['attributes']['name'],
                    'description' => $server['attributes']['description'],
                    'suspended' => $server['attributes']['suspended'] ? now() : null,
                ]);

                return $serverModel->fresh();
            }

            return null;
        })->filter()->values();

        if ($updatedServers->isEmpty()) {
            return response()->noContent();
        }

        return response()->json($updatedServers);
    }

    private function shouldUpdateServer(Server $server, array $serverData): bool
    {
        $isSuspended = $serverData['attributes']['suspended'] ? true : false;

        return $server->name !== $serverData['attributes']['name']
            || $server->description !== $serverData['attributes']['description']
            || (!$isSuspended && !is_null($server->suspended))
            || ($isSuspended && is_null($server->suspended));
    }
}
