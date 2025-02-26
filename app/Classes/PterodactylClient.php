<?php

namespace App\Classes;

use App\Models\Pterodactyl\Egg;
use App\Models\Pterodactyl\Nest;
use App\Models\Pterodactyl\Node;
use App\Models\Product;
use App\Models\Server;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use App\Settings\PterodactylSettings;
use App\Settings\ServerSettings;

class PterodactylClient
{
    //TODO: Extend error handling (maybe logger for more errors when debugging)

    private int $per_page_limit = 200;

    private int $allocation_limit = 200;

    public PendingRequest $client;

    public PendingRequest $application;

    public function __construct(PterodactylSettings $ptero_settings)
    {
        $server_settings = new ServerSettings();

        try {
            $this->client = $this->client($ptero_settings);
            $this->application = $this->clientAdmin($ptero_settings);
            $this->per_page_limit = $ptero_settings->per_page_limit;
            $this->allocation_limit = $server_settings->allocation_limit;
        } catch (Exception $exception) {
            logger('Failed to construct Pterodactyl client, Settings table not available?', ['exception' => $exception]);
        }
    }
    /**
     * @return PendingRequest
     */
    public function client(PterodactylSettings $ptero_settings)
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $ptero_settings->user_token,
            'Content-type' => 'application/json',
            'Accept' => 'Application/vnd.pterodactyl.v1+json',
        ])->baseUrl($ptero_settings->getUrl() . 'api' . '/');
    }

    public function clientAdmin(PterodactylSettings $ptero_settings)
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $ptero_settings->admin_token,
            'Content-type' => 'application/json',
            'Accept' => 'Application/vnd.pterodactyl.v1+json',
        ])->baseUrl($ptero_settings->getUrl() . 'api' . '/');
    }

    /**
     * @return Exception
     */
    private function getException(string $message = '', int $status = 0): Exception
    {
        if ($status == 404) {
            return new Exception('Resource does not exist on pterodactyl - ' . $message, 404);
        }

        if ($status == 403) {
            return new Exception('No permission on pterodactyl, check pterodactyl token and permissions - ' . $message, 403);
        }

        if ($status == 401) {
            return new Exception('No pterodactyl token set - ' . $message, 401);
        }

        if ($status == 500) {
            return new Exception('Pterodactyl server error - ' . $message, 500);
        }

        if ($status == 0) {
            return new Exception('Unable to connect to Pterodactyl node - Please check if the node is online and accessible', 503);
        }

        if ($status >= 500 && $status < 600) {
            return new Exception('Pterodactyl node error (HTTP ' . $status . ') - ' . $message, $status);
        }

        return new Exception('Request Failed, is pterodactyl set-up correctly? - ' . $message);
    }

    /**
     * @param  Nest  $nest
     * @return mixed
     *
     * @throws Exception
     */
    public function getEggs(Nest $nest)
    {
        try {
            $response = $this->application->get("application/nests/{$nest->id}/eggs?include=nest,variables&per_page=" . $this->per_page_limit);
        } catch (Exception $e) {
            throw self::getException($e->getMessage());
        }
        if ($response->failed()) {
            throw self::getException('Failed to get eggs from pterodactyl - ', $response->status());
        }

        return $response->json()['data'];
    }

    /**
     * @return mixed
     *
     * @throws Exception
     */
    public function getNodes()
    {
        try {
            $response = $this->application->get('application/nodes?per_page=' . $this->per_page_limit);
        } catch (Exception $e) {
            throw self::getException($e->getMessage());
        }
        if ($response->failed()) {
            throw self::getException('Failed to get nodes from pterodactyl - ', $response->status());
        }

        return $response->json()['data'];
    }

    /**
     * @return mixed
     *
     * @throws Exception
     * @description Returns the infos of a single node
     */
    public function getNode($id)
    {
        try {
            $response = $this->application->get('application/nodes/' . $id);
        } catch (Exception $e) {
            throw self::getException($e->getMessage());
        }
        if ($response->failed()) {
            throw self::getException('Failed to get node id ' . $id . ' - ' . $response->status());
        }

        return $response->json()['attributes'];
    }

    public function getServers()
    {
        try {
            $response = $this->application->get('application/servers?per_page=' . $this->per_page_limit);
        } catch (Exception $e) {
            throw self::getException($e->getMessage());
        }
        if ($response->failed()) {
            throw self::getException('Failed to get list of servers - ', $response->status());
        }

        return $response->json()['data'];
    }

    /**
     * @return null
     *
     * @throws Exception
     */
    public function getNests()
    {
        try {
            $response = $this->application->get('application/nests?per_page=' . $this->per_page_limit);
        } catch (Exception $e) {
            throw self::getException($e->getMessage());
        }
        if ($response->failed()) {
            throw self::getException('Failed to get nests from pterodactyl', $response->status());
        }

        return $response->json()['data'];
    }

    /**
     * @return mixed
     *
     * @throws Exception
     */
    public function getLocations()
    {
        try {
            $response = $this->application->get('application/locations?per_page=' . $this->per_page_limit);
        } catch (Exception $e) {
            throw self::getException($e->getMessage());
        }
        if ($response->failed()) {
            throw self::getException('Failed to get locations from pterodactyl - ', $response->status());
        }

        return $response->json()['data'];
    }

    /**
     * @param  Node  $node
     * @return mixed
     *
     * @throws Exception
     */
    public function getFreeAllocationId(Node $node)
    {
        return self::getFreeAllocations($node)[0]['attributes']['id'] ?? null;
    }

    /**
     * @param  Node  $node
     * @return array|mixed|null
     *
     * @throws Exception
     */
    public function getFreeAllocations(Node $node)
    {
        $response = self::getAllocations($node);
        $freeAllocations = [];

        if (isset($response['data'])) {
            if (!empty($response['data'])) {
                foreach ($response['data'] as $allocation) {
                    if (!$allocation['attributes']['assigned']) {
                        array_push($freeAllocations, $allocation);
                    }
                }
            }
        }

        return $freeAllocations;
    }

    /**
     * @param  Node  $node
     * @return array|mixed
     *
     * @throws Exception
     */
    public function getAllocations(Node $node)
    {
        try {
            $response = $this->application->get("application/nodes/{$node->id}/allocations?per_page={$this->allocation_limit}");
        } catch (Exception $e) {
            throw self::getException($e->getMessage());
        }
        if ($response->failed()) {
            throw self::getException('Failed to get allocations from pterodactyl - ', $response->status());
        }

        return $response->json();
    }

    /**
     * @param  Server  $server
     * @param  Egg  $egg
     * @param  int  $allocationId
     * @return Response
     */
    public function createServer(Server $server, Egg $egg, int $allocationId, mixed $eggVariables = null)
    {
       try {
            $response = $this->application->post('application/servers', [
                'name' => $server->name,
                'external_id' => $server->id,
                'user' => $server->user->pterodactyl_id,
                'egg' => $egg->id,
                'docker_image' => $egg->docker_image,
                'startup' => $egg->startup,
                'environment' => $this->getEnvironmentVariables($egg, $eggVariables),
                'oom_disabled' => !$server->product->oom_killer,
                'limits' => [
                    'memory' => $server->product->memory,
                    'swap' => $server->product->swap,
                    'disk' => $server->product->disk,
                    'io' => $server->product->io,
                    'cpu' => $server->product->cpu,
                ],
                'feature_limits' => [
                    'databases' => $server->product->databases,
                    'backups' => $server->product->backups,
                    'allocations' => $server->product->allocations,
                ],
                'allocation' => [
                    'default' => $allocationId,
                ],
            ]);

            if ($response->failed()) {
                throw self::getException('Failed to create server on pterodactyl', $response->status());
            }

            return $response;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function suspendServer(Server $server)
    {
        try {
            $response = $this->application->post("application/servers/$server->pterodactyl_id/suspend");
        } catch (Exception $e) {
            throw self::getException($e->getMessage());
        }
        if ($response->failed()) {
            throw self::getException('Failed to suspend server from pterodactyl - ', $response->status());
        }

        return $response;
    }

    public function unSuspendServer(Server $server)
    {
        try {
            $response = $this->application->post("application/servers/$server->pterodactyl_id/unsuspend");
        } catch (Exception $e) {
            throw self::getException($e->getMessage());
        }
        if ($response->failed()) {
            throw self::getException('Failed to unsuspend server from pterodactyl - ', $response->status());
        }

        return $response;
    }

    /**
     * Get user by pterodactyl id
     *
     * @param  int  $pterodactylId
     * @return mixed
     */
    public function getUser(int $pterodactylId)
    {
        try {
            $response = $this->application->get("application/users/{$pterodactylId}");
        } catch (Exception $e) {
            throw self::getException($e->getMessage());
        }
        if ($response->failed()) {
            throw self::getException('Failed to get user from pterodactyl - ', $response->status());
        }

        return $response->json()['attributes'];
    }

    /**
     * Get serverAttributes by pterodactyl id
     *
     * @param  int  $pterodactylId
     * @return mixed
     */
    public function getServerAttributes(int $pterodactylId, bool $deleteOn404 = false)
    {
        try {
            $response = $this->application->get("application/servers/{$pterodactylId}?include=egg,node,nest,location");
        } catch (Exception $e) {
            throw self::getException($e->getMessage());
        }

        //print response body

        if ($response->failed()) {
            if ($deleteOn404) {  //Delete the server if it does not exist (server deleted on pterodactyl)
                Server::where('pterodactyl_id', $pterodactylId)->first()->delete();

                return;
            } else {
                throw self::getException('Failed to get server attributes from pterodactyl - ', $response->status());
            }
        }

        return $response->json()['attributes'];
    }

    /**
     * Update Server Resources
     *
     * @param  Server  $server
     * @param  Product  $product
     * @return Response
     */
    public function updateServer(Server $server, Product $product)
    {
        return $this->application->patch("application/servers/{$server->pterodactyl_id}/build", [
            'allocation' => $server->allocation,
            'memory' => $product->memory,
            'swap' => $product->swap,
            'disk' => $product->disk,
            'io' => $product->io,
            'cpu' => $product->cpu,
            'threads' => null,
            'oom_disabled' => !$server->product->oom_killer,
            'feature_limits' => [
                'databases' => $product->databases,
                'backups' => $product->backups,
                'allocations' => $product->allocations,
            ],
        ]);
    }

    /**
     * Update the owner of a server
     *
     * @param  int  $userId
     * @param  Server  $server
     * @return mixed
     */
    public function updateServerOwner(Server $server, int $userId)
    {
        return $this->application->patch("application/servers/{$server->pterodactyl_id}/details", [
            'name' => $server->name,
            'user' => $userId,
        ]);
    }

    /**
     * Power Action Specific Server
     *
     * @param  Server  $server
     * @param  string  $action
     * @return Response
     */
    public function powerAction(Server $server, $action)
    {
        return $this->client->post("client/servers/{$server->identifier}/power", [
            'signal' => $action,
        ]);
    }

    /**
     * Get info about user
     */
    public function getClientUser()
    {
        return $this->client->get('client/account');
    }

    /**
     * Check if node has enough free resources to allocate the given resources
     *
     * @param  Node  $node
     * @param  int  $requireMemory
     * @param  int  $requireDisk
     * @return bool
     */
    public function checkNodeResources(Node $node, int $requireMemory, int $requireDisk)
    {
        try {
            $response = $this->application->get("application/nodes/{$node->id}");
        } catch (Exception $e) {
            throw self::getException($e->getMessage());
        }
        $node = $response['attributes'];
        $freeMemory = ($node['memory'] * ($node['memory_overallocate'] + 100) / 100) - $node['allocated_resources']['memory'];
        $freeDisk = ($node['disk'] * ($node['disk_overallocate'] + 100) / 100) - $node['allocated_resources']['disk'];
        if ($freeMemory < $requireMemory) {
            return false;
        }
        if ($freeDisk < $requireDisk) {
            return false;
        }

        return true;
    }

    private function getEnvironmentVariables(Egg $egg, $variables)
    {
        $environment = [];
        $variables = json_decode($variables, true);

        foreach ($egg->environment as $envVariable) {
            $matchedVariable = collect($variables)->firstWhere('env_variable', $envVariable['env_variable']);

            $environment[$envVariable['env_variable']] = $matchedVariable
                ? $matchedVariable['filled_value']
                : $envVariable['default_value'];
        }

        return $environment;
    }
}
