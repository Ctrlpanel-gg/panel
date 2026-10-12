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

            // Guard: the current product row may have been deleted. Fail
            // early with a clear error so later property access and the
            // compensation path never receive null instead of a Product.
            $oldProduct = $server->product;
            if (!$oldProduct) {
                throw new ServerUpgradeException('Server upgrade failed. Please try again later.', 500);
            }

            // Fetch remote state once, reuse for heal check and node check.
            $pterodactylServer = $this->pterodactylClient->getServerAttributes($server->pterodactyl_id);
            $pterodactylServerAllocation = $pterodactylServer['allocation'];

            // Self-heal: a previous attempt may have applied the new build
            // remotely while the local transaction failed and the restore
            // also failed (local old, remote new). Restore remote to the
            // billed product before proceeding. Skipped when remote exposes
            // no limits data. Heal failure only warns; the upgrade's own
            // build update sets the correct state or fails itself.
            if (!$this->isBuildInSync($pterodactylServer, $oldProduct)) {
                try {
                    $this->pterodactylClient->updateServerBuild(
                        $server->pterodactyl_id,
                        $pterodactylServerAllocation,
                        $oldProduct
                    );
                } catch (\Throwable $healException) {
                    logger()->warning('Server build diverged and heal failed, proceeding with upgrade', [
                        'server_id' => $server->id,
                        'pterodactyl_id' => $server->pterodactyl_id,
                        'error' => $healException->getMessage(),
                    ]);
                }
                $pterodactylServer = $this->pterodactylClient->getServerAttributes($server->pterodactyl_id);
                $pterodactylServerAllocation = $pterodactylServer['allocation'];
            }

            // Idempotency: If server is already on the target product, return without charging again.
            // Product IDs are compared as strings so retries carrying the
            // same ID in a different scalar type are still recognised.
            if ($this->isSameProduct($server, $product)) {
                return $server;
            }

            // Validate without charging. Returns the computed price.
            $finalPrice = $this->validateAndPrepare($user, $product, $server);

            $pterodactylServerNodeId = $pterodactylServer['relationships']['node']['attributes']['id'];
            $node = Node::findOrFail($pterodactylServerNodeId);

            // Check if the new product can be applied to the server.
            $requiredMemory = $product->memory - $oldProduct->memory;
            $requiredDisk = $product->disk - $oldProduct->disk;

            if (!$this->pterodactylClient->checkNodeResources($node, $requiredMemory, $requiredDisk)) {
                throw new InsufficientResourcesException('Insufficient resources on the node to upgrade the server.');
            }

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

            // Restart is best-effort, failure only logged. Runs after the
            // local commit, so a throw here must not report the upgrade
            // as failed (retry is idempotent via isSameProduct anyway).
            try {
                $powerActionResponse = $this->pterodactylClient->powerAction($server, 'restart');
                if ($powerActionResponse->failed()) {
                    logger()->warning('Server upgraded but restart failed - user can restart manually', [
                        'pterodactyl_id' => $server->pterodactyl_id,
                        'status' => $powerActionResponse->status(),
                        'error' => $powerActionResponse->json()
                    ]);
                }
            } catch (\Throwable $e) {
                logger()->warning('Server upgraded but restart failed - user can restart manually', [
                    'pterodactyl_id' => $server->pterodactyl_id,
                    'error' => $e->getMessage(),
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

    /**
     * Whether the remote build matches the given product.
     *
     * Only keys the remote actually exposes are compared; when it exposes
     * no limits data at all it counts as matching so plain flows never
     * trigger a heal.
     */
    private function isBuildInSync(array $remote, Product $product): bool
    {
        $limits = $remote['limits'] ?? [];
        $featureLimits = $remote['feature_limits'] ?? [];

        foreach (['memory' => $product->memory, 'swap' => $product->swap, 'disk' => $product->disk, 'io' => $product->io, 'cpu' => $product->cpu] as $key => $expected) {
            if (array_key_exists($key, $limits)) {
                if ((int) $limits[$key] !== (int) $expected) {
                    return false;
                }
            }
        }

        if (array_key_exists('oom_disabled', $limits)) {
            if ((bool) $limits['oom_disabled'] !== !$product->oom_killer) {
                return false;
            }
        }

        foreach (['databases' => $product->databases, 'allocations' => $product->allocations, 'backups' => $product->backups] as $key => $expected) {
            if (array_key_exists($key, $featureLimits)) {
                if ((int) $featureLimits[$key] !== (int) $expected) {
                    return false;
                }
            }
        }

        // No comparable keys means the remote exposed no limits data:
        // count as matching so plain flows never trigger a heal.
        return true;
    }

    /** Validate upgrade and return net price. Caller charges after success. */
    private function validateAndPrepare(User $user, Product $product, Server $server): int
    {
        $billingPeriodSeconds = $this->getSecondsFromBillingPeriod($server->product);
        $timeUsed = now()->diffInSeconds($server->last_billed, true);
        $refundAmount = $server->product->price - ($server->product->price * ($timeUsed / $billingPeriodSeconds));

        $finalPrice = (int) round($product->price - $refundAmount);

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
