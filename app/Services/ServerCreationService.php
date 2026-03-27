<?php

namespace App\Services;

use App\Classes\PterodactylClient;
use App\Models\Server;
use App\Models\User;
use App\Models\Product;
use App\Models\Pterodactyl\Egg;
use App\Models\Pterodactyl\Node;
use App\Settings\GeneralSettings;
use App\Settings\PterodactylSettings;
use App\Settings\ServerSettings;
use App\Settings\UserSettings;
use Illuminate\Support\Facades\DB;

class ServerCreationService
{
    private ?UserSettings $userSettings = null;
    private ?GeneralSettings $generalSettings = null;
    private ?ServerSettings $serverSettings = null;
    private ?PterodactylClient $pterodactylClient = null;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
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
        try {
            return DB::transaction(function () use ($user, $product, $data) {
                $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
                $validatedData = $this->validateAndPrepare($lockedUser, $product, $data);
                $egg = $product->loadMissing('eggs')->eggs->firstWhere('id', $data['egg_id']);

                if (! $egg instanceof Egg) {
                    throw new \Exception('Selected egg is not available for this product.', 422);
                }

                $server = Server::create([
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'user_id' => $lockedUser->id,
                    'product_id' => $product->id,
                    'node_id' => $validatedData['node_id'],
                    'last_billed' => now(),
                    'billing_priority' => isset($data['billing_priority']) ? $data['billing_priority'] : $product->default_billing_priority,
                ]);

                $response = $this->pterodactylClient()->createServer($server, $egg, $validatedData['allocation_id'], $data['egg_variables'] ?? null);

                if ($response->failed()) {
                    logger()->error('Failed to create server on Pterodactyl', [
                        'server_id' => $server->id,
                        'status' => $response->status(),
                        'error' => $response->json()
                    ]);

                    throw new \Exception('Failed to create server on Pterodactyl.');
                }

                $serverAttributes = data_get($response->json(), 'attributes');

                if (! is_array($serverAttributes) || ! isset($serverAttributes['id'], $serverAttributes['identifier'])) {
                    throw new \Exception('Invalid response received from Pterodactyl.', 500);
                }

                $server->update([
                    'pterodactyl_id' => $serverAttributes['id'],
                    'identifier' => $serverAttributes['identifier']
                ]);

                $lockedUser->decrement('credits', $product->price);

                return $server;
            });
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
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
        // if the column is null or smaller than price, treat price as minimum
        $minCredits = ($product->minimum_credits === null || $product->minimum_credits < $product->price)
            ? $product->price
            : $product->minimum_credits;

        if ($user->credits < $minCredits) {
            throw new \Exception(
                sprintf(
                    'User do not have the required amount of %s to use this product!',
                    $this->generalSettings()->credits_display_name
                )
            );
        }

        // General checks for user.
        if (!$user->hasVerifiedEmail() && $this->userSettings()->force_email_verification) {
            throw new \Exception('User must verify their email before creating a server.');
        }

        if (!$user->discordUser && $this->userSettings()->force_discord_verification) {
            throw new \Exception('User must link their Discord account before creating a server.');
        }

        if ($user->cannot("admin.servers.bypass_creation_enabled") && !$this->serverSettings()->creation_enabled) {
            throw new \Exception('Server creation is currently disabled.');
        }

        // Check if the product is available in the user's location.
        $availableNode = $this->findAvailableNode($data['location_id'], $product);

        if (!$availableNode) {
            throw new \Exception('No available nodes for this product in the selected location.');
        }

        $allocationId = $this->pterodactylClient()->getFreeAllocationId($availableNode);

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
            return !$this->pterodactylClient()->checkNodeResources($node, $product->memory, $product->disk);
        });

        return $availableNodes->isEmpty() ? null : $availableNodes->first();
    }

    private function userSettings(): UserSettings
    {
        return $this->userSettings ??= app(UserSettings::class);
    }

    private function generalSettings(): GeneralSettings
    {
        return $this->generalSettings ??= app(GeneralSettings::class);
    }

    private function serverSettings(): ServerSettings
    {
        return $this->serverSettings ??= app(ServerSettings::class);
    }

    private function pterodactylClient(): PterodactylClient
    {
        if ($this->pterodactylClient === null) {
            $this->pterodactylClient = app(PterodactylClient::class, [app(PterodactylSettings::class)]);
        }

        return $this->pterodactylClient;
    }
}
