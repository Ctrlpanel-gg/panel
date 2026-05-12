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
        $egg = null;

        $lockKey = "server_provisioning_user_{$user->id}";
        $lock = Cache::lock($lockKey, 30);

        try {
            $lock->block(10);
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $exception) {
            throw new \Exception('Another provisioning request is in progress for this user. Please try again in a few seconds.');
        }

        $server = null;
        $creditsReserved = false;

        try {
            $validatedData = $this->validateAndPrepare($user, $product, $data);

            $egg = $product->eggs()->find($data['egg_id']);
            if (!$egg) {
                throw new \Exception('Egg not attached to this product.');
            }

            $credits = (int) round($product->price);

            // Reserve credits first (outside server insert transaction) to avoid lock ordering issues
            // between users and servers tables under high concurrency.
            $this->reserveCredits($user, $credits);
            $creditsReserved = true;

            DB::transaction(function () use ($user, $product, $validatedData, $data, &$server) {
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

            if (!$server) {
                throw new \Exception('Server creation record failed to persist.');
            }

            try {
                $response = $this->pterodactylClient->createServer($server, $egg, $validatedData['allocation_id'], $validatedData['egg_variables']);
            } catch (\Throwable $e) {
                return $this->handleProvisionUncertain($server, $credits, $e);
            }

            if ($response->successful()) {
                return $this->handleProvisionSuccess($server, $response, $credits);
            }

            return $this->handleProvisionFailure($server, $response, $credits);
        } catch (\Throwable $e) {
            if ($creditsReserved) {
                if ($server && $server->exists) {
                    if ($server->status !== Server::STATUS_ACTIVE && $server->status !== Server::STATUS_FAILED) {
                        $server->update(['status' => Server::STATUS_PENDING_RECONCILIATION]);
                        dispatch(new ReconcileServerCreationJob($server->id, $credits));
                    }
                } elseif (!$server || !$server->exists) {
                    $this->refundCredits($user, $credits);
                }
            }
            throw $e;
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

    private function reserveCredits(User $user, int $price): void
    {
        $this->creditService->reserve($user, $price);
    }

    private function refundCredits(User $user, int $price): void
    {
        $this->creditService->refund($user, $price);
    }

    private function handleProvisionSuccess(Server $server, $response, int $chargedPrice): Server
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

    private function handleProvisionFailure(Server $server, $response, int $chargedPrice): Server
    {
        logger()->error('Server creation failed on Pterodactyl (Permanent Error)', [
            'server_id' => $server->id,
            'status' => $response->status(),
            'error' => $response->json(),
        ]);

        // If Pterodactyl returned a 400 Bad Request, it means the request was invalid (e.g. missing variables).
        // In this case, we know the server wasn't created, so we can immediately delete it.
        if ($response->status() === 400) {
            $server->delete();

            throw new \Exception(__('Server could not be created, please try again later or contact administration if the issue persists.'));
        }

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
                $server->delete();

                throw new \Exception(__('Server could not be created, please try again later or contact administration if the issue persists.'));
            }

            $server->update(['status' => Server::STATUS_PENDING_RECONCILIATION]);
            dispatch(new ReconcileServerCreationJob($server->id, $chargedPrice));

            return $server;
        } catch (\Throwable $e) {
            if ($e instanceof \Exception) {
                throw $e;
            }
            return $this->handleProvisionUncertain($server, $chargedPrice, $e);
        }
    }

    /**
     * Handle a provisioning state where the outcome is uncertain (e.g. timeout, 500).
     */
    private function handleProvisionUncertain(Server $server, int $chargedPrice, \Throwable $exception): Server
    {
        logger()->warning('Provisioning uncertain (Timeout/Transient error), scheduling reconciliation', [
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
            // Check if node has enough memory and disk resources
            if (!$this->pterodactylClient->checkNodeResources($node, $product->memory, $product->disk)) {
                return true;
            }

            // Check if node has free allocations (IP/port)
            $freeAllocations = $this->pterodactylClient->getFreeAllocations($node);
            return empty($freeAllocations);
        });

        return $availableNodes->isEmpty() ? null : $availableNodes->first();
    }
}
