<?php

namespace App\Services;

use App\Classes\PterodactylClient;
use App\Exceptions\Server\InsufficientCreditsException;
use App\Exceptions\Server\InsufficientResourcesException;
use App\Exceptions\Pterodactyl\PterodactylException;
use App\Exceptions\Server\ServerUpgradeException;
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
     * @throws PterodactylException
     * @throws ServerUpgradeException
     */
    public function handle(User $user, Product $product, Server $server): Server
    {
        try {
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

            // Apply the new resource limits on Pterodactyl. Throws a
            // PterodactylException on failure so the server is left untouched.
            $this->pterodactylClient->updateServerBuild($server->pterodactyl_id, $pterodactylServerAllocation, $product);

            // Update local DB immediately so Pterodactyl and panel are in sync
            // even if the restart below fails (restart can be retried manually).
            $server->update([
                'product_id' => $product->id,
                'last_billed' => now(),
                'canceled' => null
            ]);

            // Charge credits only after Pterodactyl + local DB succeeded.
            if ($finalPrice > 0) {
                $user->decrement('credits', $finalPrice);
            } elseif ($finalPrice < 0) {
                $user->increment('credits', abs($finalPrice));
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
        } catch (\Exception $e) {
            throw new ServerUpgradeException($e->getMessage(), $e->getCode() ?: 500, $e);
        }
    }

    /** Validate upgrade and return net price. Caller charges after success. */
    private function validateAndPrepare(User $user, Product $product, Server $server): float
    {
        $billingPeriodSeconds = $this->getSecondsFromBillingPeriod($product);
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
