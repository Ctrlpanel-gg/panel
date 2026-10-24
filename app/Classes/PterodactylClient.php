<?php

namespace App\Classes;

use App\Exceptions\Pterodactyl\PterodactylAuthenticationException;
use App\Exceptions\Pterodactyl\PterodactylConnectionException;
use App\Exceptions\Pterodactyl\PterodactylException;
use App\Exceptions\Pterodactyl\PterodactylNotFoundException;
use App\Exceptions\Pterodactyl\PterodactylPermissionException;
use App\Exceptions\Pterodactyl\PterodactylServerException;
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
    private int $per_page_limit = 200;

    private int $allocation_limit = 200;

    public PendingRequest $client;

    public PendingRequest $application;

    /**
     * Per-request HTTP timeout in seconds. 0 leaves the HTTP default untouched.
     */
    public int $requestTimeout = 0;

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
     * Bound the HTTP timeout for later calls on this instance only.
     *
     * @param  int  $seconds  0 disables it (default).
     */
    public function setRequestTimeout(int $seconds): static
    {
        $this->requestTimeout = $seconds;

        return $this;
    }

    /**
     * Apply the timeout on a clone; the shared request stays untouched.
     */
    protected function withTimeout(PendingRequest $request): PendingRequest
    {
        if ($this->requestTimeout <= 0) {
            return $request;
        }

        return (clone $request)->timeout($this->requestTimeout);
    }

    /**
     * Map the HTTP status to a typed exception and throw it.
     *
     * @param  string  $message
     * @param  int|null  $status
     * @param  \Throwable|null  $previous
     * @return never
     */
    private static function throwException(string $message = '', ?int $status = null, ?\Throwable $previous = null): never
    {
        if ($status == 404) {
            throw new PterodactylNotFoundException(
                'Resource does not exist on Pterodactyl - ' . $message . ' Was a Server deleted from Pterodactyl but not from the Panel? Have an Admin Remove it from the Panel',
                $previous
            );
        }

        if ($status == 403) {
            throw new PterodactylPermissionException(
                'No permission on Pterodactyl, check Pterodactyl token and permissions - ' . $message,
                $previous
            );
        }

        if ($status == 401) {
            throw new PterodactylAuthenticationException(
                'No Pterodactyl token set - ' . $message,
                $previous
            );
        }

        if ($status === null) {
            throw new PterodactylConnectionException(
                'Unable to connect to Pterodactyl node - Please check if the node is online and accessible - ' . $message,
                $previous
            );
        }

        if ($status >= 500 && $status < 600) {
            throw new PterodactylServerException(
                'Pterodactyl node error (HTTP ' . $status . ') - ' . $message,
                $status,
                $previous
            );
        }

        throw new PterodactylException(
            'Request Failed, is Pterodactyl set-up correctly? - ' . $message,
            $status,
            $previous
        );
    }

    /**
     * Throw for a failed response. Body goes to logs only (capped);
     * clients see getPublicMessage().
     */
    private static function throwForResponse(string $message, Response $response): never
    {
        $body = substr($response->body(), 0, 2000);

        self::throwException(
            $body !== '' ? rtrim($message) . ' ' . $body : $message,
            $response->status()
        );
    }

    /**
     * @param  Nest  $nest
     * @return mixed
     *
     * @throws PterodactylException
     */
    public function getEggs(Nest $nest)
    {
        try {
            $response = $this->application->get("application/nests/{$nest->id}/eggs?include=nest,variables&per_page=" . $this->per_page_limit);
        } catch (Exception $e) {
            self::throwException($e->getMessage(), null, $e);
        }
        if ($response->failed()) {
            self::throwForResponse('Failed to get eggs from pterodactyl -', $response);
        }

        return $response->json()['data'];
    }

    /**
     * @return mixed
     *
     * @throws PterodactylException
     */
    public function getNodes()
    {
        try {
            $response = $this->application->get('application/nodes?per_page=' . $this->per_page_limit);
        } catch (Exception $e) {
            self::throwException($e->getMessage(), null, $e);
        }
        if ($response->failed()) {
            self::throwForResponse('Failed to get nodes from pterodactyl -', $response);
        }

        return $response->json()['data'];
    }

    /**
     * @return mixed
     *
     * @throws PterodactylException
     * @description Returns the infos of a single node
     */
    public function getNode($id)
    {
        try {
            $response = $this->application->get('application/nodes/' . $id);
        } catch (Exception $e) {
            self::throwException($e->getMessage(), null, $e);
        }
        if ($response->failed()) {
            self::throwForResponse('Failed to get node id ' . $id . ' -', $response);
        }

        return $response->json()['attributes'];
    }

    public function getServers()
    {
        try {
            $response = $this->application->get('application/servers?per_page=' . $this->per_page_limit);
        } catch (Exception $e) {
            self::throwException($e->getMessage(), null, $e);
        }
        if ($response->failed()) {
            self::throwForResponse('Failed to get list of servers -', $response);
        }

        return $response->json()['data'];
    }

    /**
     * Fetch all pages of a Pterodactyl list endpoint. Reconcile callers must use getAll* variants.
     *
     * @throws PterodactylException
     */
    private function fetchAllPages(string $path): array
    {
        $items = [];
        $page = 1;

        do {
            $separator = str_contains($path, '?') ? '&' : '?';

            try {
                $response = $this->application->get("{$path}{$separator}page={$page}&per_page={$this->per_page_limit}");
            } catch (Exception $e) {
                self::throwException($e->getMessage(), null, $e);
            }

            if ($response->failed()) {
                self::throwForResponse('Failed to fetch paginated resource from pterodactyl -', $response);
            }

            $json = $response->json();

            foreach ($json['data'] ?? [] as $item) {
                $items[] = $item;
            }

            $totalPages = $json['meta']['pagination']['total_pages'] ?? 1;
            $page++;
        } while ($page <= $totalPages);

        return $items;
    }

    /**
     * @throws PterodactylException
     */
    public function getAllServers(): array
    {
        return $this->fetchAllPages('application/servers');
    }

    /**
     * @throws PterodactylException
     */
    public function getAllNodes(): array
    {
        return $this->fetchAllPages('application/nodes');
    }

    /**
     * @throws PterodactylException
     */
    public function getAllNests(): array
    {
        return $this->fetchAllPages('application/nests');
    }

    /**
     * @throws PterodactylException
     */
    public function getAllLocations(): array
    {
        return $this->fetchAllPages('application/locations');
    }

    /**
     * @throws PterodactylException
     */
    public function getAllEggs(Nest $nest): array
    {
        return $this->fetchAllPages("application/nests/{$nest->id}/eggs?include=nest,variables");
    }

    /**
     * @return array
     *
     * @throws PterodactylException
     */
    public function getNests()
    {
        try {
            $response = $this->application->get('application/nests?per_page=' . $this->per_page_limit);
        } catch (Exception $e) {
            self::throwException($e->getMessage(), null, $e);
        }
        if ($response->failed()) {
            self::throwForResponse('Failed to get nests from pterodactyl', $response);
        }

        return $response->json()['data'];
    }

    /**
     * @return mixed
     *
     * @throws PterodactylException
     */
    public function getLocations()
    {
        try {
            $response = $this->application->get('application/locations?per_page=' . $this->per_page_limit);
        } catch (Exception $e) {
            self::throwException($e->getMessage(), null, $e);
        }
        if ($response->failed()) {
            self::throwForResponse('Failed to get locations from pterodactyl -', $response);
        }

        return $response->json()['data'];
    }

    /**
     * @param  Node  $node
     * @return mixed
     */
    public function getFreeAllocationId(Node $node)
    {
        return self::getFreeAllocations($node)[0]['attributes']['id'] ?? null;
    }

    /**
     * @param  Node  $node
     * @return array
     */
    public function getFreeAllocations(Node $node)
    {
        try {
            $response = $this->getAllocations($node);
        } catch (Exception) {
            return [];
        }

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
     * True when the node is at its allocation limit (0 means no limit).
     *
     * @param  Node  $node
     * @return bool
     */
    public function nodeHasReachedAllocationLimit(Node $node): bool
    {
        // A limit of 0 means "no limit", so the node is never blocked.
        $limit = $node->allocation_limit;
        if ($limit <= 0) {
            return false;
        }

        try {
            $response = $this->getAllocations($node);
        } catch (Exception) {
            return true;
        }

        if (!isset($response['data']) || empty($response['data'])) {
            return false;
        }

        $usedAllocations = 0;
        foreach ($response['data'] as $allocation) {
            if ($allocation['attributes']['assigned']) {
                $usedAllocations++;
            }
        }

        return $usedAllocations >= $limit;
    }

    /**
     * @param  Node  $node
     * @return array|mixed
     *
     * @throws PterodactylException
     */
    public function getAllocations(Node $node)
    {
        try {
            $response = $this->application->get("application/nodes/{$node->id}/allocations?per_page={$this->per_page_limit}");
        } catch (Exception $e) {
            self::throwException($e->getMessage(), null, $e);
        }
        if ($response->failed()) {
            self::throwForResponse('Failed to get allocations from pterodactyl -', $response);
        }

        return $response->json();
    }

    /**
     * Create a server on Pterodactyl.
     *
     * @param  Server  $server
     * @param  Egg  $egg
     * @param  int  $allocationId
     * @param  mixed  $eggVariables
     * @return Response
     *
     * @throws PterodactylException
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
        } catch (Exception $e) {
            self::throwException(
                'Failed to create server on Pterodactyl - ' . $e->getMessage(),
                null,
                $e
            );
        }

        return $response;
    }

    /**
     * Get a server from Pterodactyl by its external ID.
     *
     * @param  string  $externalId
     * @return Response
     *
     * @throws PterodactylException
     */
    public function getServerByExternalId(string $externalId)
    {
        try {
            $response = $this->application->get("application/servers/external/{$externalId}");
        } catch (Exception $e) {
            self::throwException(
                'Failed to get server by external_id from Pterodactyl - ' . $e->getMessage(),
                null,
                $e
            );
        }

        return $response;
    }

    public function suspendServer(Server $server)
    {
        try {
            $response = $this->application->post("application/servers/$server->pterodactyl_id/suspend");
        } catch (Exception $e) {
            self::throwException($e->getMessage(), null, $e);
        }
        if ($response->failed()) {
            self::throwForResponse('Failed to suspend server from pterodactyl -', $response);
        }

        return $response;
    }

    public function unSuspendServer(Server $server)
    {
        try {
            $response = $this->application->post("application/servers/$server->pterodactyl_id/unsuspend");
        } catch (Exception $e) {
            self::throwException($e->getMessage(), null, $e);
        }
        if ($response->failed()) {
            self::throwForResponse('Failed to unsuspend server from pterodactyl -', $response);
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
            self::throwException($e->getMessage(), null, $e);
        }
        if ($response->failed()) {
            self::throwForResponse('Failed to get user from pterodactyl -', $response);
        }

        return $response->json()['attributes'];
    }

    /**
     * Update user on Pterodactyl
     *
     * @param int $pterodactylId
     * @param array $data
     * @throws \App\Exceptions\Pterodactyl\PterodactylException
     * @return \Illuminate\Http\Client\Response
     */
    public function updateUser(int $pterodactylId, array $data)
    {
        try {
            $response = $this->application->patch("application/users/{$pterodactylId}", $data);
        } catch (Exception $e) {
            self::throwException($e->getMessage(), null, $e);
        }
        if ($response->failed()) {
            self::throwForResponse('Failed to update user on pterodactyl -', $response);
        }

        return $response;
    }

    /**
     * Pure read, never deletes. Callers delete only on PterodactylNotFoundException.
     *
     * @param  int  $pterodactylId
     * @return mixed
     */
    public function getServerAttributes(int $pterodactylId)
    {
        try {
            $response = $this->withTimeout($this->application)->get("application/servers/{$pterodactylId}?include=egg,node,nest,location");
        } catch (Exception $e) {
            self::throwException($e->getMessage(), null, $e);
        }

        if ($response->failed()) {
            self::throwForResponse('Failed to get server attributes from pterodactyl -', $response);
        }

        return $response->json()['attributes'];
    }

    /**
     * Update Server Resources
     *
     * @param  Server  $server
     * @param  Product  $product
     * @return Response
     *
     * @deprecated Use updateServerBuild instead.
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
     * Update server build.
     *
     * @param  string  $pterodactylId
     * @param  int  $pterodactylAllocation
     * @param  Product  $product
     * @return Response
     *
     * @throws PterodactylException
     */
    public function updateServerBuild(string $pterodactylId, int $pterodactylAllocation, Product $product)
    {
        try {
            $response = $this->withTimeout($this->application)->patch("application/servers/{$pterodactylId}/build", [
                'allocation' => $pterodactylAllocation,
                'memory' => $product->memory,
                'swap' => $product->swap,
                'disk' => $product->disk,
                'io' => $product->io,
                'cpu' => $product->cpu,
                'threads' => null,
                'oom_disabled' => !$product->oom_killer,
                'feature_limits' => [
                    'databases' => $product->databases,
                    'backups' => $product->backups,
                    'allocations' => $product->allocations,
                ],
            ]);
        } catch (Exception $e) {
            self::throwException($e->getMessage(), null, $e);
        }

        if ($response->failed()) {
            self::throwForResponse('Failed to update server build on Pterodactyl', $response);
        }

        return $response;
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
     * Update server details
     *
     * @param  Server  $server
     * @param  array  $data
     * @return Response
     *
     * @throws PterodactylException
     */
    public function updateServerDetails(Server $server, array $data)
    {
        try {
            $response = $this->application->patch("application/servers/{$server->pterodactyl_id}/details", $data);
        } catch (Exception $e) {
            self::throwException('Failed to update server details on Pterodactyl - ' . $e->getMessage(), null, $e);
        }

        if ($response->failed()) {
            self::throwForResponse('Failed to update server details on Pterodactyl', $response);
        }

        return $response;
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
        return $this->withTimeout($this->client)->post("client/servers/{$server->identifier}/power", [
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
     * False when resources are short or cannot be determined (fail closed).
     *
     * @param  Node  $node
     * @param  int  $requireMemory
     * @param  int  $requireDisk
     * @return bool
     */
    public function checkNodeResources(Node $node, int $requireMemory, int $requireDisk)
    {
        try {
            $response = $this->withTimeout($this->application)->get("application/nodes/{$node->id}");
        } catch (Exception $e) {
            self::throwException($e->getMessage(), null, $e);
        }

        if ($response->failed()) {
            return false;
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
        // Support for front-end and api variables format.
        $variables = collect(is_string($variables) ? json_decode($variables, true) : $variables);

        foreach ($egg->environment as $envVariable) {
            if ($envVariable['default_value'] !== null && $envVariable['default_value'] !== '') {
                $environment[$envVariable['env_variable']] = $envVariable['default_value'];
            }
        }

        $environment = array_merge($environment, $variables->toArray());

        return $environment;
    }
}
