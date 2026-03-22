<?php

namespace App\Services;

use App\Classes\PterodactylClient;
use App\Models\Server;
use App\Models\User;
use App\Models\Product;
use App\Models\Pterodactyl\Node;
use App\Jobs\PostServerCreationJob;
use App\Jobs\ReconcileServerCreationJob;
use App\Services\CreditService;
use App\Settings\GeneralSettings;
use App\Settings\PterodactylSettings;
use App\Settings\ServerSettings;
use App\Settings\UserSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServerCreationService
{
    private PterodactylSettings $pterodactylSettings;
    private UserSettings $userSettings;
    private GeneralSettings $generalSettings;
    private ServerSettings $serverSettings;
    private PterodactylClient $pterodactylClient;
    private CreditService $creditService;

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
        $this->creditService = app(CreditService::class);
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

        $lockKey = "server_provisioning_user_{$user->id}";
        $lock = Cache::lock($lockKey, 30);

        if (!$lock->block(10)) {
            throw new \Exception('Another provisioning request is in progress for this user.');
        }

        $server = null;
        $creditsReserved = false;

        try {
            $validatedData = $this->validateAndPrepare($user, $product, $data);

            DB::transaction(function () use ($user, $product, $validatedData, $data, &$server, &$creditsReserved) {
                $this->reserveCredits($user, $product->price);
                $creditsReserved = true;

                $server = Server::create([
                    'name' => $data['name'],
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'node_id' => $validatedData['node_id'],
                    'last_billed' => now(),
                    'billing_priority' => $validatedData['billing_priority'],
                    'status' => Server::STATUS_PROVISIONING,
                ]);
            });

            try {
                $response = $this->pterodactylClient->createServer($server, $egg, $validatedData['allocation_id'], $validatedData['egg_variables'] ?? null);

                if ($response->successful()) {
                    return $this->handleProvisionSuccess($server, $response, $product->price);
                }

                return $this->handleProvisionFailure($server, $user, $product, $response);
            } catch (\Exception $e) {
                return $this->handleProvisionUncertain($server, $product->price, $e);
            }
        } catch (\Exception $e) {
            if ($creditsReserved && is_null($server)) {
                $this->refundCredits($user, $product->price);
            }
            throw new \Exception($e->getMessage());
        } finally {
            $lock->release();
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
            'billing_priority' => $data['billing_priority'] ?? $product->default_billing_priority,
            'egg_variables' => $data['egg_variables'] ?? null,
        ];
    }

    private function reserveCredits(User $user, float $price): void
    {
        $this->creditService->reserve($user, $price);
    }

    private function refundCredits(User $user, float $price): void
    {
        $this->creditService->refund($user, $price);
    }

    private function handleProvisionSuccess(Server $server, $response, float $chargedPrice): Server
    {
        $serverAttributes = $response->json()['attributes'] ?? null;

        if (!$serverAttributes || !isset($serverAttributes['id']) || !isset($serverAttributes['identifier'])) {
            throw new \Exception('Invalid response from Pterodactyl on server creation.');
        }

        try {
            $server->update([
                'pterodactyl_id' => $serverAttributes['id'],
                'identifier' => $serverAttributes['identifier'],
                'status' => Server::STATUS_ACTIVE,
            ]);

            dispatch(new PostServerCreationJob($server->id));

            return $server;
        } catch (\Throwable $e) {
            logger()->error('Failed to persist successful provisioning state', [
                'server_id' => $server->id,
                'error' => $e->getMessage(),
            ]);

            $server->update(['status' => Server::STATUS_PENDING_RECONCILIATION]);
            dispatch(new ReconcileServerCreationJob($server->id, $chargedPrice));

            return $server;
        }
    }

    private function handleProvisionFailure(Server $server, User $user, Product $product, $response): Server
    {
        logger()->warning('Provisioning failed on Pterodactyl, re-checking remote state', [
            'server_id' => $server->id,
            'status' => $response->status(),
            'error' => $response->json(),
        ]);

        try {
            $remoteResponse = $this->pterodactylClient->getServerByExternalId($server->id);

            if ($remoteResponse->successful()) {
                $remoteAttributes = $remoteResponse->json()['attributes'] ?? null;

                if ($remoteAttributes && isset($remoteAttributes['id']) && isset($remoteAttributes['identifier'])) {
                    $server->update([
                        'pterodactyl_id' => $remoteAttributes['id'],
                        'identifier' => $remoteAttributes['identifier'],
                        'status' => Server::STATUS_ACTIVE,
                    ]);

                    dispatch(new PostServerCreationJob($server->id));

                    return $server;
                }
            }

            if ($remoteResponse->status() === 404) {
                $this->refundCredits($user, $product->price);
                $server->update(['status' => Server::STATUS_FAILED]);

                throw new \Exception('Server creation failed and did not exist on remote; user credits have been refunded.');
            }

            $server->update(['status' => Server::STATUS_PENDING_RECONCILIATION]);
            dispatch(new ReconcileServerCreationJob($server->id, $product->price));

            return $server;
        } catch (\Exception $e) {
            return $this->handleProvisionUncertain($server, $product->price, $e);
        }
    }

    /**
     * Handle a provisioning state where the outcome is uncertain.
     *
     * The passed exception is intentionally only used for logging and is not rethrown
     * or further analyzed here. At this point we cannot reliably determine the remote
     * Pterodactyl state, so we mark the server as pending reconciliation and delegate
     * detailed error handling and state correction to ReconcileServerCreationJob.
     */
    private function handleProvisionUncertain(Server $server, float $chargedPrice, \Exception $exception): Server
    {
        logger()->warning('Provisioning uncertain, scheduling reconciliation', [
            'server_id' => $server->id,
            'exception' => $exception->getMessage(),
        ]);

        $server->update(['status' => Server::STATUS_PENDING_RECONCILIATION]);
        dispatch(new ReconcileServerCreationJob($server->id, $chargedPrice));

        return $server;
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
