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
                $finalPrice = $this->calculateFinalPrice($lockedUser, $product, $lockedServer);

                $pterodactylServer = $this->pterodactylClient()->getServerAttributes($lockedServer->pterodactyl_id);

                $pterodactylServerNodeId = $pterodactylServer['relationships']['node']['attributes']['id'];
                $node = Node::findOrFail($pterodactylServerNodeId);

                $requiredMemory = $product->memory - $lockedServer->product->memory;
                $requiredDisk = $product->disk - $lockedServer->product->disk;

                if (! $this->pterodactylClient()->checkNodeResources($node, $requiredMemory, $requiredDisk)) {
                    throw new \Exception('Insufficient resources on the node to upgrade the server.', 422);
                }

                $pterodactylServerAllocation = $pterodactylServer['allocation'];

                $updateServerResponse = $this->pterodactylClient()->updateServerBuild($lockedServer->pterodactyl_id, $pterodactylServerAllocation, $product);

                if ($updateServerResponse->failed()) {
                    logger()->error('Failed to update server on Pterodactyl', [
                        'pterodactyl_id' => $lockedServer->pterodactyl_id,
                        'status' => $updateServerResponse->status(),
                        'error' => $updateServerResponse->json()
                    ]);

                    throw new \Exception(
                        sprintf(
                            'Failed to update server on Pterodactyl: %s',
                            $updateServerResponse->json()['errors'][0]['detail'] ?? 'Unknown error'
                        )
                    );
                }

                $powerActionResponse = $this->pterodactylClient()->powerAction($lockedServer, 'restart');

                if ($powerActionResponse->failed()) {
                    logger()->error('Failed to restart server on Pterodactyl', [
                        'pterodactyl_id' => $lockedServer->pterodactyl_id,
                        'status' => $powerActionResponse->status(),
                        'error' => $powerActionResponse->json()
                    ]);

                    throw new \Exception(
                        sprintf(
                            'Failed to restart server on Pterodactyl: %s',
                            $powerActionResponse->json()['errors'][0]['detail'] ?? 'Unknown error'
                        )
                    );
                }

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

                return $lockedServer;
            });
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    private function calculateFinalPrice(User $user, Product $product, Server $server): float
    {
        $billingPeriodSeconds = $this->getSecondsFromBillingPeriod($product);
        $timeUsed = now()->diffInSeconds($server->last_billed, true);
        $refundAmount = $server->product->price - ($server->product->price * ($timeUsed / $billingPeriodSeconds));
        $finalPrice = $product->price - $refundAmount;

        if ($user->credits < $finalPrice) {
            throw new \Exception('Insufficient credits to upgrade the server.', 422);
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

    private function pterodactylClient(): PterodactylClient
    {
        if ($this->pterodactylClient === null) {
            $this->pterodactylClient = app(PterodactylClient::class, [app(PterodactylSettings::class)]);
        }

        return $this->pterodactylClient;
    }
}
