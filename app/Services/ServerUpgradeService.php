<?php

namespace App\Services;

use App\Classes\PterodactylClient;
use App\Models\Server;
use App\Models\User;
use App\Models\Product;
use App\Models\Pterodactyl\Node;
use App\Settings\PterodactylSettings;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\DB;

class ServerUpgradeService
{
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
     * @param Server $server
     * @return Server
     * 
     * @throws \Exception
     */
    public function handle(User $user, Product $product, Server $server): Server
    {
        try {
            return DB::transaction(function () use ($user, $product, $server) {
                $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
                $lockedServer = Server::query()->whereKey($server->id)->lockForUpdate()->firstOrFail();
                $lockedServer->loadMissing('product');
                $currentProduct = $lockedServer->product;

                if (! $currentProduct instanceof Product) {
                    throw new \Exception('The current server product could not be resolved.', 500);
                }

                $finalPrice = $this->calculateFinalPrice($lockedUser, $product, $lockedServer);

                $pterodactylServer = $this->pterodactylClient()->getServerAttributes($lockedServer->pterodactyl_id);
                $pterodactylServerNodeId = data_get($pterodactylServer, 'relationships.node.attributes.id');

                if (! is_int($pterodactylServerNodeId) && ! ctype_digit((string) $pterodactylServerNodeId)) {
                    throw new \Exception('Failed to resolve the current Pterodactyl node for this server.', 500);
                }

                $pterodactylServerNodeId = (int) $pterodactylServerNodeId;
                $node = Node::findOrFail($pterodactylServerNodeId);
                $currentEggId = data_get($pterodactylServer, 'egg');

                if (! $product->nodes()->whereKey($node->id)->exists()) {
                    throw new \Exception('Selected product is not available on the current node.', 422);
                }

                if ($currentEggId !== null && ! $product->eggs()->whereKey($currentEggId)->exists()) {
                    throw new \Exception('Selected product is not compatible with the current egg.', 422);
                }

                $requiredMemory = $product->memory - $currentProduct->memory;
                $requiredDisk = $product->disk - $currentProduct->disk;

                if (! $this->pterodactylClient()->checkNodeResources($node, $requiredMemory, $requiredDisk)) {
                    throw new \Exception('Insufficient resources on the node to upgrade the server.', 422);
                }

                $pterodactylServerAllocation = data_get($pterodactylServer, 'allocation');

                if (! is_int($pterodactylServerAllocation) && ! ctype_digit((string) $pterodactylServerAllocation)) {
                    throw new \Exception('Failed to resolve the current Pterodactyl allocation for this server.', 500);
                }

                $pterodactylServerAllocation = (int) $pterodactylServerAllocation;

                if ($finalPrice > 0) {
                    $lockedUser->decrement('credits', $finalPrice);
                } elseif ($finalPrice < 0) {
                    $lockedUser->increment('credits', abs($finalPrice));
                }

                $lockedServer->update([
                    'product_id' => $product->id,
                    'last_billed' => now(),
                    'canceled' => null
                ]);

                $updateServerResponse = $this->pterodactylClient()->updateServerBuild($lockedServer->pterodactyl_id, $pterodactylServerAllocation, $product);

                if ($updateServerResponse->failed()) {
                    logger()->error('Failed to update server on Pterodactyl', [
                        'pterodactyl_id' => $lockedServer->pterodactyl_id,
                        'status' => $updateServerResponse->status(),
                        'error' => $updateServerResponse->json()
                    ]);

                    throw new \Exception(
                        'Failed to update server on Pterodactyl.',
                        500
                    );
                }

                $powerActionResponse = $this->pterodactylClient()->powerAction($lockedServer, 'restart');

                if ($powerActionResponse->failed()) {
                    try {
                        $this->pterodactylClient()->updateServerBuild($lockedServer->pterodactyl_id, $pterodactylServerAllocation, $currentProduct);
                    } catch (\Throwable $rollbackException) {
                        logger()->error('Failed to roll back server build after restart failure.', [
                            'pterodactyl_id' => $lockedServer->pterodactyl_id,
                            'error' => $rollbackException->getMessage(),
                        ]);
                    }

                    logger()->error('Failed to restart server on Pterodactyl', [
                        'pterodactyl_id' => $lockedServer->pterodactyl_id,
                        'status' => $powerActionResponse->status(),
                        'error' => $powerActionResponse->json()
                    ]);

                    throw new \Exception(
                        'Failed to restart server on Pterodactyl.',
                        500
                    );
                }

                return $lockedServer;
            });
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    private function calculateFinalPrice(User $user, Product $product, Server $server): int
    {
        $billingPeriodSeconds = $this->getSecondsFromBillingPeriod($product);
        $timeUsed = min(now()->diffInSeconds($server->last_billed, true), $billingPeriodSeconds);
        $unusedRatio = max(0, ($billingPeriodSeconds - $timeUsed) / $billingPeriodSeconds);
        $refundAmount = (int) round($server->product->price * $unusedRatio);
        $finalPrice = $product->price - $refundAmount;

        if ($finalPrice > 0 && $user->credits < $finalPrice) {
            throw new \Exception('Insufficient credits to upgrade the server.', 422);
        }

        return (int) round($finalPrice);
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

    private function pterodactylClient(): PterodactylClient
    {
        if ($this->pterodactylClient === null) {
            $this->pterodactylClient = app(PterodactylClient::class, [app(PterodactylSettings::class)]);
        }

        return $this->pterodactylClient;
    }
}
