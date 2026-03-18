<?php

namespace App\Services;

use App\Classes\PterodactylClient;
use App\Models\Server;
use App\Models\User;
use App\Models\Product;
use App\Models\Pterodactyl\Node;
use App\Settings\PterodactylSettings;
use Carbon\CarbonInterval;

class ServerUpgradeService
{
    private PterodactylSettings $pterodactylSettings;
    private PterodactylClient $pterodactylClient;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->pterodactylSettings = app(PterodactylSettings::class);
        $this->pterodactylClient = app(PterodactylClient::class, [$this->pterodactylSettings]);
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
            $this->validateAndPrepare($user, $product, $server);

            $pterodactylServer = $this->pterodactylClient->getServerAttributes($server->pterodactyl_id);

            $pterodactylServerNodeId = $pterodactylServer['relationships']['node']['attributes']['id'];
            $node = Node::findOrFail($pterodactylServerNodeId);

            // Check if the new product can be applied to the server.
            $requiredMemory = $product->memory - $server->product->memory;
            $requiredDisk = $product->disk - $server->product->disk;

            if (!$this->pterodactylClient->checkNodeResources($node, $requiredMemory, $requiredDisk)) {
                throw new \Exception('Insufficient resources on the node to upgrade the server.', 422);
            }

            $pterodactylServerAllocation = $pterodactylServer['allocation'];

            $updateServerResponse = $this->pterodactylClient->updateServerBuild($server->pterodactyl_id, $pterodactylServerAllocation, $product);
            
            if ($updateServerResponse->failed()) {
                logger()->error('Failed to update server on Pterodactyl', [
                    'pterodactyl_id' => $server->pterodactyl_id,
                    'status' => $updateServerResponse->status(),
                    'error' => $updateServerResponse->json()
                ]);

                $server->delete();

                throw new \Exception(
                    sprintf(
                        'Failed to update server on Pterodactyl: %s',
                        $updateServerResponse->json()['errors'][0]['detail'] ?? 'Unknown error'
                    )
                );
            }

            $powerActionResponse = $this->pterodactylClient->powerAction($server, 'restart');

            if ($powerActionResponse->failed()) {
                logger()->error('Failed to restart server on Pterodactyl', [
                    'pterodactyl_id' => $server->pterodactyl_id,
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

            $server->update([
                'product_id' => $product->id,
                'last_billed' => now(),
                'canceled' => null
            ]);

            return $server;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    private function validateAndPrepare(User $user, Product $product, Server $server): void
    {
        // Check if user has enough credits to upgrade the server.
        $billingPeriodSeconds = $this->getSecondsFromBillingPeriod($product);
        $timeUsed = now()->diffInSeconds($server->last_billed, true);
        $refundAmount = $server->product->price - ($server->product->price * ($timeUsed / $billingPeriodSeconds));

        if ($user->credits < ($product->price - $refundAmount)) {
            throw new \Exception('Insufficient credits to upgrade the server.', 422);
        }

        // Refund the user for the unused time on the current product.
        $finalPrice = $product->price - $refundAmount;
        if ($finalPrice > 0) {
            $user->decrement('credits', $finalPrice);
        } elseif ($finalPrice < 0) {
            $user->increment('credits', abs($finalPrice));
        }
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
