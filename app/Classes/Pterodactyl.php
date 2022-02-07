<?php

namespace App\Classes;

use App\Models\Egg;
use App\Models\Nest;
use App\Models\Node;
use App\Models\Server;
use App\Models\Settings;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Pterodactyl
{
    /**
     * @description per_page option to pull more than the default 50 from pterodactyl
     */
    public const PER_PAGE = 200;

    //TODO: Extend error handling (maybe logger for more errors when debugging)

    /**
     * @return PendingRequest
     */
    public static function client()
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . config("SETTINGS::SYSTEM:PTERODACTYL:TOKEN"),
            'Content-type'  => 'application/json',
            'Accept'        => 'Application/vnd.pterodactyl.v1+json',
        ])->baseUrl(config("SETTINGS::SYSTEM:PTERODACTYL:URL") . '/api');
    }

    /**
     * @return Exception
     */
    private static function getException(): Exception
    {
        return new Exception('Request Failed, is pterodactyl set-up correctly?');
    }

    /**
     * @param Nest $nest
     * @return mixed
     * @throws Exception
     */
    public static function getEggs(Nest $nest)
    {
        try {
            $response = self::client()->get("/application/nests/{$nest->id}/eggs?include=nest,variables&per_page=" . self::PER_PAGE);
        } catch (Exception $e) {
            throw self::getException();
        }
        if ($response->failed()) throw self::getException();
        return $response->json()['data'];
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public static function getNodes()
    {
        try {
            $response = self::client()->get('/application/nodes?per_page=' . self::PER_PAGE);
        } catch (Exception $e) {
            throw self::getException();
        }
        if ($response->failed()) throw self::getException();
        return $response->json()['data'];
    }

    /**
     * @return null
     * @throws Exception
     */
    public static function getNests()
    {
        try {
            $response = self::client()->get('/application/nests?per_page=' . self::PER_PAGE);
        } catch (Exception $e) {
            throw self::getException();
        }
        if ($response->failed()) throw self::getException();
        return $response->json()['data'];
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public static function getLocations()
    {
        try {
            $response = self::client()->get('/application/locations?per_page=' . self::PER_PAGE);
        } catch (Exception $e) {
            throw self::getException();
        }
        if ($response->failed()) throw self::getException();

        return $response->json()['data'];
    }

    /**
     * @param Node $node
     * @return mixed
     * @throws Exception
     */
    public static function getFreeAllocationId(Node $node)
    {
        return self::getFreeAllocations($node)[0]['attributes']['id'] ?? null;
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
     * @param Node $node
     * @return array|mixed
     * @throws Exception
     */
    public static function getAllocations(Node $node)
    {
        $per_page = config('SETTINGS::SERVER:ALLOCATION_LIMIT', 200);
        try {
            $response = self::client()->get("/application/nodes/{$node->id}/allocations?per_page={$per_page}");
        } catch (Exception $e) {
            throw self::getException();
        }
        if ($response->failed()) throw self::getException();

        return $response->json();
    }

    /**
     * @param String $route
     * @return string
     */
    public static function url(string $route): string
    {
        return config("SETTINGS::SYSTEM:PTERODACTYL:URL") . $route;
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
        try {
            $response = self::client()->post("/application/servers/$server->pterodactyl_id/suspend");
        } catch (Exception $e) {
            throw self::getException();
        }
        if ($response->failed()) throw self::getException();

        return $response;
    }

    public static function unSuspendServer(Server $server)
    {
        try {
            $response = self::client()->post("/application/servers/$server->pterodactyl_id/unsuspend");
        } catch (Exception $e) {
            throw self::getException();
        }
        if ($response->failed()) throw self::getException();

        return $response;
    }

    /**
     * Get user by pterodactyl id
     * @param int $pterodactylId
     * @return mixed
     */
    public function getUser(int $pterodactylId)
    {
        try {
            $response = self::client()->get("/application/users/{$pterodactylId}");
        } catch (Exception $e) {
            throw self::getException();
        }
        if ($response->failed()) throw self::getException();

        return $response->json()['attributes'];
    }

    /**
     * Get serverAttributes by pterodactyl id
     * @param int $pterodactylId
     * @return mixed
     */
    public static function getServerAttributes(int $pterodactylId)
    {
        try {
            $response = self::client()->get("/application/servers/{$pterodactylId}?include=egg,node,nest,location");
        } catch (Exception $e) {
            throw self::getException();
        }
        if ($response->failed()) throw self::getException();
        return $response->json()['attributes'];
    }
}
