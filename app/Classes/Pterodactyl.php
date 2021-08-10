<?php

namespace App\Classes;

use App\Models\Configuration;
use App\Models\Egg;
use App\Models\Nest;
use App\Models\Node;
use App\Models\Server;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Validator;

class Pterodactyl
{
    /**
     * @return PendingRequest
     */
    public static function client()
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . env('PTERODACTYL_TOKEN', false),
            'Content-type'  => 'application/json',
            'Accept'        => 'Application/vnd.pterodactyl.v1+json',
        ])->baseUrl(env('PTERODACTYL_URL') . '/api');
    }

    //TODO: Extend error handling (maybe logger for more errors when debugging)
    /**
     * Get user by pterodactyl id
     * @param int $pterodactylId
     * @return mixed
     */
    public function getUser(int $pterodactylId)
    {
        $response = self::client()->get("/application/users/{$pterodactylId}");

        if ($response->failed()) return $response->json();
        return $response->json()['attributes'];
    }

    /**
     * @param Node $node
     * @return array|mixed|null
     * @throws Exception
     */
    public static function getFreeAllocations(Node $node)
    {
        $response = self::getAllocations($node);
        $freeAllocations = [];

        if (isset($response['data'])) {
            if (!empty($response['data'])) {
                foreach ($response['data'] as $allocation) {
                    if (!$allocation['attributes']['assigned']) array_push($freeAllocations, $allocation);
                }
            }
        }

        return $freeAllocations;
    }

    /**
     * @return null
     * @throws Exception
     */
    public static function getNests()
    {
        $response = self::client()->get('/application/nests');
        if ($response->failed()) throw self::getException();
        return $response->json()['data'];
    }

    /**
     * @param Nest $nest
     * @return mixed
     * @throws Exception
     */
    public static function getEggs(Nest $nest)
    {
        $response = self::client()->get("/application/nests/{$nest->id}/eggs?include=nest,variables");
        if ($response->failed()) throw self::getException();
        return $response->json()['data'];
    }


    /**
     * @return mixed
     * @throws Exception
     */
    public static function getNodes()
    {
        $response = self::client()->get('/application/nodes');
        if ($response->failed()) throw self::getException();
        return $response->json()['data'];
    }


    /**
     * @return mixed
     * @throws Exception
     */
    public static function getLocations()
    {
        $response = self::client()->get('/application/locations');
        if ($response->failed()) throw self::getException();
        return $response->json()['data'];
    }

    /**
     * @param Node $node
     * @return mixed
     */
    public static function getFreeAllocationId(Node $node)
    {

        return self::getFreeAllocations($node)[0]['attributes']['id'] ?? null;
    }


    /**
     * @param Node $node
     * @throws Exception
     */
    public static function getAllocations(Node $node)
    {
        $per_page = Configuration::getValueByKey('ALLOCATION_LIMIT', 200);
        $response = self::client()->get("/application/nodes/{$node->id}/allocations?per_page={$per_page}");
        if ($response->failed()) throw self::getException();
        return $response->json();
    }


    /**
     * @param String $route
     * @return string
     */
    public static function url(string $route): string
    {
        return env('PTERODACTYL_URL') . $route;
    }

    /**
     * @param Server $server
     * @param Egg $egg
     * @param int $allocationId
     * @return Response
     */
    public static function createServer(Server $server, Egg $egg, int $allocationId)
    {
        return self::client()->post("/application/servers", [
            "name"           => $server->name,
            "external_id"    => $server->id,
            "user"           => $server->user->pterodactyl_id,
            "egg"            => $egg->id,
            "docker_image"   => $egg->docker_image,
            "startup"        => $egg->startup,
            "environment"    => $egg->getEnvironmentVariables(),
            "limits"         => [
                "memory" => $server->product->memory,
                "swap"   => $server->product->swap,
                "disk"   => $server->product->disk,
                "io"     => $server->product->io,
                "cpu"    => $server->product->cpu
            ],
            "feature_limits" => [
                "databases"   => $server->product->databases,
                "backups"     => $server->product->backups,
                "allocations" => $server->product->allocations,
            ],
            "allocation"     => [
                "default" => $allocationId
            ]
        ]);
    }

    public static function suspendServer(Server $server)
    {
        $response = self::client()->post("/application/servers/$server->pterodactyl_id/suspend");
        if ($response->failed()) throw self::getException();
        return $response;
    }

    public static function unSuspendServer(Server $server)
    {
        $response = self::client()->post("/application/servers/$server->pterodactyl_id/unsuspend");
        if ($response->failed()) throw self::getException();
        return $response;
    }

    /**
     * @return Exception
     */
    private static function getException(): Exception
    {
        return new Exception('Request Failed, is pterodactyl set-up correctly?');
    }
}
