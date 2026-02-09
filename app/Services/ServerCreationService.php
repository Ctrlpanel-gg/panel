<?php

namespace App\Services;

use App\Classes\PterodactylClient;
use App\Models\Server;
use App\Models\User;
use App\Models\Product;
use App\Models\Pterodactyl\Node;
use App\Settings\GeneralSettings;
use App\Settings\PterodactylSettings;
use App\Settings\ServerSettings;
use App\Settings\UserSettings;

class ServerCreationService
{
    private PterodactylSettings $pterodactylSettings;
    private UserSettings $userSettings;
    private GeneralSettings $generalSettings;
    private ServerSettings $serverSettings;
    private PterodactylClient $pterodactylClient;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->pterodactylSettings = app(PterodactylSettings::class);
        $this->userSettings = app(UserSettings::class);
        $this->generalSettings = app(GeneralSettings::class);
        $this->serverSettings = app(ServerSettings::class);
        $this->pterodactylClient = app(PterodactylClient::class, [$this->pterodactylSettings]);
    }

    /**
     * Handle the server creation process.
     *
     * @param User $user
     * @param Product $product
     * @param mixed $data
     * @return Server
     * 
     * @throws \Exception
     */
    public function handle(User $user, Product $product, mixed $data): Server
    {
        $egg = $product->eggs->firstWhere('id', $data['egg_id']);

        try {
            $validatedData = $this->validateAndPrepare($user, $product, $data);

            $server = Server::create([
                'name' => $data['name'],
                'user_id' => $user->id,
                'product_id' => $product->id,
                'node_id' => $validatedData['node_id'],
                'last_billed' => now(),
                'billing_priority' => isset($data['billing_priority']) ? $data['billing_priority'] : $product->default_billing_priority,
            ]);

            $response = $this->pterodactylClient->createServer($server, $egg, $validatedData['allocation_id'], isset($data['egg_variables']) ? $data['egg_variables'] : null);

            if ($response->failed()) {
                logger()->error('Failed to create server on Pterodactyl', [
                    'server_id' => $server->id,
                    'status' => $response->status(),
                    'error' => $response->json()
                ]);

                $server->delete();

                throw new \Exception('Failed to create server on Pterodactyl: ' . $response->json()['errors'][0]['detail'] ?? 'Unknown error');
            }

            $serverAttributes = $response->json()['attributes'];
            $server->update([
                'pterodactyl_id' => $serverAttributes['id'],
                'identifier' => $serverAttributes['identifier']
            ]);

            return $server;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function validateAndPrepare(User $user, Product $product, mixed $data): mixed
    {
        // Check if user has reached server limit.
        $currentUserServersCount = $user->servers()->count();

        if ($currentUserServersCount >= $user->server_limit) {
            throw new \Exception('Server limit reached for this product.');
        }

        // Check if user has reached product specific server limit.
        $userProductServersCount = $user->servers()->where("product_id", $product->id)->count();

        if ($product->serverlimit > 0 && $userProductServersCount >= $product->serverlimit) {
            throw new \Exception('Server limit reached for this user and product combination.');
        }

        // Check if user has enough credits to create the server.
        $minCredits = $product->minimum_credits ?: $this->userSettings->min_credits_to_make_server;

        if ($user->credits < $minCredits) {
            throw new \Exception(
                sprintf(
                    'User do not have the required amount of %s to use this product!',
                    $this->generalSettings->credits_display_name
                )
            );
        }

        // General checks for user.
        if (!$user->hasVerifiedEmail() && $this->userSettings->force_email_verification) {
            throw new \Exception('User must verify their email before creating a server.');
        }

        if (!$user->discordUser && $this->userSettings->force_discord_verification) {
            throw new \Exception('User must link their Discord account before creating a server.');
        }

        if ($user->cannot("admin.servers.bypass_creation_enabled") && !$this->serverSettings->creation_enabled) {
            throw new \Exception('Server creation is currently disabled.');
        }

        // Check if the product is available in the user's location.
        $availableNode = $this->findAvailableNode($data['location_id'], $product);

        if (!$availableNode) {
            throw new \Exception('No available nodes for this product in the selected location.');
        }

        $allocationId = $this->pterodactylClient->getFreeAllocationId($availableNode);

        if (!$allocationId) {
            throw new \Exception('No free allocation available on the selected node.');
        }

        return [
            'allocation_id' => $allocationId,
            'node_id' => $availableNode->id,
        ];
    }

    private function findAvailableNode(string $locationId, Product $product): ?Node
    {
        $nodes = Node::where('location_id', $locationId)
            ->whereHas('products', fn($q) => $q->where('product_id', $product->id))
            ->get();

        $availableNodes = $nodes->reject(function ($node) use ($product) {
            return !$this->pterodactylClient->checkNodeResources($node, $product->memory, $product->disk);
        });

        return $availableNodes->isEmpty() ? null : $availableNodes->first();
    }
}
