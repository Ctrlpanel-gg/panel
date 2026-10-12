<?php

namespace App\Services;

use App\Classes\PterodactylClient;
use App\Exceptions\Server\InsufficientCreditsException;
use App\Exceptions\Server\InsufficientResourcesException;
use App\Exceptions\Server\ServerException;
use App\Exceptions\Pterodactyl\PterodactylException;
use App\Exceptions\Server\ServerUpgradeException;
use App\Models\Server;
use App\Models\User;
use App\Models\Product;
use App\Models\Pterodactyl\Node;
use App\Settings\PterodactylSettings;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServerUpgradeService
{
    private PterodactylSettings $pterodactylSettings;
    private PterodactylClient $pterodactylClient;

    /**
     * Bound for each Pterodactyl HTTP call in the upgrade flow. Pins the
     * HTTP client's default (30s total / 10s connect) explicitly so the
     * lease sizing below does not silently depend on a framework default.
     */
    private const PTERODACTYL_TIMEOUT_SECONDS = 30;

    /**
     * Lock lease in seconds. Sized with a safety margin over the
     * worst-case bounded operation (up to 5 remote calls x timeout plus
     * the local transaction). This reduces the chance of the lease
     * expiring mid-operation but cannot guarantee it: a hung local
     * database or a paused process can still outlive any TTL.
     */
    private const LOCK_TTL_SECONDS = 300;

    /**
     * Create a new class instance.
     */
    public function __construct(
        ?PterodactylSettings $pterodactylSettings = null,
        ?PterodactylClient $pterodactylClient = null
    ) {
        $this->pterodactylSettings = $pterodactylSettings ?? app(PterodactylSettings::class);
        $this->pterodactylClient = $pterodactylClient ?? app(PterodactylClient::class, [$this->pterodactylSettings]);
    }

    /**
     * Handle the server creation process.
     *
     * @param User $user
     * @param Product $product
     * @param Server $server
     * @return Server
     *
     * @throws PterodactylException
     * @throws ServerUpgradeException
     */
    public function handle(User $user, Product $product, Server $server): Server
    {
        // Per-server lock so concurrent upgrades of the same server are
        // serialised. Only effective across hosts when the cache backend
        // is shared (e.g. Redis); the local file/array drivers lock
        // per-node only. The TTL is a backstop for crashed holders with a
        // safety margin over the bounded operation, not a guarantee.
        $lockKey = 'server_upgrade_' . $server->id;
        $lock = Cache::lock($lockKey, self::LOCK_TTL_SECONDS);

        try {
            $lock->block(10);
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $exception) {
            throw new ServerUpgradeException('Another upgrade request is in progress for this server. Please try again in a few seconds.', 429);
        }

        try {
            // Refresh server model to get authoritative product relationship under lock
            $server->refresh();

            // Bound every remote call in this flow so a hung Pterodactyl
            // cannot outlive the lock lease. Scoped to this service's own
            // client instance; other Pterodactyl flows are untouched.
            $this->pterodactylClient->setRequestTimeout(self::PTERODACTYL_TIMEOUT_SECONDS);

            // Idempotency: If server is already on the target product, return without charging again.
            // Product IDs are compared as strings so retries carrying the
            // same ID in a different scalar type are still recognised.
            // This only suppresses duplicate charging; it does not assert
            // that the remote Pterodactyl build is in sync (out-of-band
            // divergence needs separate reconciliation, see below).
            if ($this->isSameProduct($server, $product)) {
                return $server;
            }

            // Validate without charging. Returns the computed price.
            $finalPrice = $this->validateAndPrepare($user, $product, $server);

            $pterodactylServer = $this->pterodactylClient->getServerAttributes($server->pterodactyl_id);

            $pterodactylServerNodeId = $pterodactylServer['relationships']['node']['attributes']['id'];
            $node = Node::findOrFail($pterodactylServerNodeId);

            // Check if the new product can be applied to the server.
            $requiredMemory = $product->memory - $server->product->memory;
            $requiredDisk = $product->disk - $server->product->disk;

            if (!$this->pterodactylClient->checkNodeResources($node, $requiredMemory, $requiredDisk)) {
                throw new InsufficientResourcesException('Insufficient resources on the node to upgrade the server.');
            }

            $pterodactylServerAllocation = $pterodactylServer['allocation'];
            $oldProduct = $server->product;

            // Remote changes need compensation if the local transaction fails.
            $this->pterodactylClient->updateServerBuild($server->pterodactyl_id, $pterodactylServerAllocation, $product);

            try {
                DB::transaction(function () use ($server, $product, $user, $finalPrice) {
                    $server->update([
                        'product_id' => $product->id,
                        'last_billed' => now(),
                        'canceled' => null
                    ]);

                    if ($finalPrice > 0) {
                        $charged = User::whereKey($user->id)
                            ->where('credits', '>=', $finalPrice)
                            ->decrement('credits', $finalPrice);

                        if ($charged !== 1) {
                            throw new InsufficientCreditsException('Insufficient credits to upgrade the server.');
                        }
                    } elseif ($finalPrice < 0) {
                        User::whereKey($user->id)->increment('credits', abs($finalPrice));
                    }
                });
            } catch (\Throwable $e) {
                try {
                    $this->pterodactylClient->updateServerBuild(
                        $server->pterodactyl_id,
                        $pterodactylServerAllocation,
                        $oldProduct
                    );
                } catch (\Throwable $rollbackException) {
                    logger()->critical('Server upgrade compensation failed', [
                        'server_id' => $server->id,
                        'pterodactyl_id' => $server->pterodactyl_id,
                        'error' => $rollbackException->getMessage(),
                        'original_error' => $e->getMessage(),
                    ]);
                }

                throw $e;
            }

            // Restart is best-effort, failure only logged.
            $powerActionResponse = $this->pterodactylClient->powerAction($server, 'restart');
            if ($powerActionResponse->failed()) {
                logger()->warning('Server upgraded but restart failed - user can restart manually', [
                    'pterodactyl_id' => $server->pterodactyl_id,
                    'status' => $powerActionResponse->status(),
                    'error' => $powerActionResponse->json()
                ]);
            }

            return $server;
        } catch (PterodactylException $e) {
            throw $e;
        } catch (ServerException $e) {
            throw $e;
        } catch (\Exception $e) {
            $code = $e->getCode();
            $statusCode = is_int($code) && $code >= 400 && $code < 600 ? $code : 500;
            throw new ServerUpgradeException(
                'Server upgrade failed. Please try again later.',
                $statusCode,
                $e
            );
        } finally {
            optional($lock)->release();
        }
    }

    /** Whether the server already targets the given product (ID comparison is type-agnostic). */
    private function isSameProduct(Server $server, Product $product): bool
    {
        return (string) $server->product_id === (string) $product->id;
    }

    /** Validate upgrade and return net price. Caller charges after success. */
    private function validateAndPrepare(User $user, Product $product, Server $server): float
    {
        $billingPeriodSeconds = $this->getSecondsFromBillingPeriod($server->product);
        $timeUsed = now()->diffInSeconds($server->last_billed, true);
        $refundAmount = $server->product->price - ($server->product->price * ($timeUsed / $billingPeriodSeconds));

        $finalPrice = $product->price - $refundAmount;

        if ($finalPrice > 0 && $user->credits < $finalPrice) {
            throw new InsufficientCreditsException('Insufficient credits to upgrade the server.');
        }

        return $finalPrice;
    }

    private function getSecondsFromBillingPeriod(Product $product): int
    {
        return match ($product->billing_period) {
            'hourly' => CarbonInterval::hour()->totalSeconds,
            'daily' => CarbonInterval::day()->totalSeconds,
            'weekly' => CarbonInterval::week()->totalSeconds,
            'monthly' => CarbonInterval::month()->totalSeconds,
            'quarterly' => CarbonInterval::months(3)->totalSeconds,
            'half-annually' => CarbonInterval::months(6)->totalSeconds,
            'annually' => CarbonInterval::year()->totalSeconds,
            default => CarbonInterval::hour()->totalSeconds,
        };
    }
}
