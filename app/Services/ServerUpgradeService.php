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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServerUpgradeService
{
    private PterodactylSettings $pterodactylSettings;
    private PterodactylClient $pterodactylClient;

    /**
     * Per-call Pterodactyl timeout. Sized so the lock lease below holds.
     */
    private const PTERODACTYL_TIMEOUT_SECONDS = 30;

    /**
     * Lock lease. Covers worst case (remote calls x timeout + transaction)
     * with margin. No TTL survives a hung DB or paused process.
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
     * Handle the server upgrade process.
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
        // Needs shared cache (Redis) across hosts.
        $lockKey = 'server_upgrade_' . $server->id;
        $lock = Cache::lock($lockKey, self::LOCK_TTL_SECONDS);

        try {
            $lock->block(10);
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $exception) {
            throw new ServerUpgradeException('Another upgrade request is in progress for this server. Please try again in a few seconds.', 429);
        }

        try {
            $server->refresh();

            $this->pterodactylClient->setRequestTimeout(self::PTERODACTYL_TIMEOUT_SECONDS);

            $oldProduct = $server->product;
            if (!$oldProduct) {
                throw new ServerUpgradeException('Server upgrade failed. Please try again later.', 500);
            }

            if (!$server->pterodactyl_id) {
                throw new ServerUpgradeException('Server upgrade failed. Please try again later.', 500);
            }

            $pterodactylServer = $this->pterodactylClient->getServerAttributes($server->pterodactyl_id);
            $pterodactylServerAllocation = $pterodactylServer['allocation'];

            // Half-applied upgrade (remote new, local old): restore billed product first.
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

            // IDs compare as strings so retries with a different scalar type still match.
            if ($this->isSameProduct($server, $product)) {
                return $server;
            }

            $finalPrice = $this->validateAndPrepare($user, $product, $server);

            $pterodactylServerNodeId = $pterodactylServer['relationships']['node']['attributes']['id'];
            $node = Node::findOrFail($pterodactylServerNodeId);

            // Check if the new product can be applied to the server.
            $requiredMemory = $product->memory - $oldProduct->memory;
            $requiredDisk = $product->disk - $oldProduct->disk;

            if (!$this->pterodactylClient->checkNodeResources($node, $requiredMemory, $requiredDisk)) {
                throw new InsufficientResourcesException('Insufficient resources on the node to upgrade the server.');
            }

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

            // Best-effort: retry stays idempotent.
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

    /**
     * Whether the server already targets the given product (ID comparison is type-agnostic).
     */
    private function isSameProduct(Server $server, Product $product): bool
    {
        return (string) $server->product_id === (string) $product->id;
    }

    /**
     * Whether the remote build matches the product. Missing keys are
     * skipped; no limits data at all counts as matching (no heal).
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

        // No limits data: count as matching, skip heal.
        return true;
    }

    /**
     * Validate upgrade and return net price. Caller charges after success.
     */
    private function validateAndPrepare(User $user, Product $product, Server $server): int
    {
        $refundAmount = $this->calculateRefund($server, $server->product);

        $finalPrice = (int) round($product->price - $refundAmount);

        if ($finalPrice > 0 && $user->credits < $finalPrice) {
            throw new InsufficientCreditsException('Insufficient credits to upgrade the server.');
        }

        return $finalPrice;
    }

    /**
     * Pro-rated refund for unused time. Shared with the web pre-check so
     * estimate and charge agree. A month is a fixed 30 days.
     */
    public function calculateRefund(Server $server, Product $oldProduct): float
    {
        $billingPeriodSeconds = self::getSecondsFromBillingPeriod($oldProduct);
        $timeUsed = now()->diffInSeconds($server->last_billed, true);

        return $oldProduct->price - ($oldProduct->price * ($timeUsed / $billingPeriodSeconds));
    }

    public static function getSecondsFromBillingPeriod(Product $product): int
    {
        return match ($product->billing_period) {
            'hourly' => 60 * 60,
            'daily' => 24 * 60 * 60,
            'weekly' => 7 * 24 * 60 * 60,
            'monthly' => 30 * 24 * 60 * 60,
            'quarterly' => 3 * 30 * 24 * 60 * 60,
            'half-annually' => 6 * 30 * 24 * 60 * 60,
            'annually' => 12 * 30 * 24 * 60 * 60,
            default => 60 * 60,
        };
    }
}
