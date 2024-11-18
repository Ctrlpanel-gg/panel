<?php

namespace App\Http\Controllers;

use App\Classes\PterodactylClient;
use App\Models\Pterodactyl\Egg;
use App\Models\Pterodactyl\Location;
use App\Models\Pterodactyl\Node;
use App\Models\Product;
use App\Models\Server;
use App\Models\User;
use App\Notifications\DynamicNotification;
use App\Settings\PterodactylSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

class ProductController extends Controller
{
    private $pterodactyl;

    public function __construct(PterodactylSettings $ptero_settings)
    {
        $this->pterodactyl = new PterodactylClient($ptero_settings);
    }

    /**
     * @description get product locations based on selected egg
     *
     * @param  Request  $request
     * @param  Egg  $egg
     * @return Collection|JsonResponse
     */
    public function getNodesBasedOnEgg(Request $request, Egg $egg)
    {
        if (is_null($egg->id)) {
            return response()->json('Egg ID is required', '400');
        }

        //get products that include this egg
        $products = Product::query()
            ->with('nodes')
            ->where('disabled', '=', false)
            ->whereHas('eggs', function (Builder $builder) use ($egg) {
                $builder->where('id', '=', $egg->id);
            })->get();

        $nodes = collect();

        //filter unique nodes
        $products->each(function (Product $product) use ($nodes) {
            $product->nodes->each(function (Node $node) use ($nodes) {
                if (! $nodes->contains('id', $node->id) && ! $node->disabled) {
                    $nodes->add($node);
                }
            });
        });

        return $nodes;
    }

    /**
     * @description get product locations based on selected egg
     *
     * @param  Request  $request
     * @param  Egg  $egg
     * @return Collection|JsonResponse
     */
    public function getLocationsBasedOnEgg(Request $request, Egg $egg)
    {
        $nodes = $this->getNodesBasedOnEgg($request, $egg);
        foreach ($nodes as $key => $node) {
            $pteroNode = $this->pterodactyl->getNode($node->id);
            if ($pteroNode['allocated_resources']['memory'] >= ($pteroNode['memory'] * ($pteroNode['memory_overallocate'] + 100) / 100) || $pteroNode['allocated_resources']['disk'] >= ($pteroNode['disk'] * ($pteroNode['disk_overallocate'] + 100) / 100)) {
                $nodes->forget($key);
            }
        }
        $locations = collect();

        //locations
        $nodes->each(function (Node $node) use ($nodes, $locations) {
            /** @var Location $location */
            $location = $node->location;

            if (! $locations->contains('id', $location->id)) {
                $nodeIds = $nodes->map(function ($node) {
                    return $node->id;
                });

                $location->nodes = $location->nodes()
                    ->whereIn('id', $nodeIds)
                    ->get();

                $locations->add($location);
            }
        });

        if($locations->isEmpty()){
            // Rate limit the node full notification to 1 attempt per 30 minutes
            RateLimiter::attempt(
                key: 'nodes-full-warning',
                maxAttempts: 1,
                callback: function() {
                    // get admin role and check users
                    $users = User::query()->where('role', '=', '1')->get();
                    Notification::send($users,new DynamicNotification(['mail'],[],
                   mail: (new MailMessage)->subject('Attention! All of the nodes are full!')->greeting('Attention!')->line('All nodes are full, please add more nodes')));
                },
                decaySeconds: 5
            );
        }

        return $locations;
    }

    /**
     * @param  Int $location
     * @param  Egg  $egg
     * @return Collection|JsonResponse
     */
    public function getProductsBasedOnLocation(Egg $egg, Int $location)
    {
        if (is_null($egg->id) || is_null($location)) {
            return response()->json('location and egg id is required', '400');
        }

        // Get all nodes in this location
        $nodes = Node::query()
            ->where('location_id', '=', $location)
            ->get();

        $user = Auth::user();
        $products = Product::query()
            ->where('disabled', '=', false)
            ->whereHas('nodes', function (Builder $builder) use ($nodes) {
                $builder->whereIn('id', $nodes->map(function ($node) {
                    return $node->id;
                }));
            })
            ->whereHas('eggs', function (Builder $builder) use ($egg) {
                $builder->where('id', '=', $egg->id);
            })
            ->get()
            ->map(function ($product) use ($user) {
                // Add servers_count to each product
                $product->servers_count = Server::where('product_id', $product->id)
                    ->where('user_id', $user->id) // Filter by user ID
                    ->count();

                return $product; // Ensure you return the modified product
            });

        // Instead of the old node check, we will check if the product fits in any given node in the location
        foreach ($products as $key => $product) {
            $product->doesNotFit = false;
            foreach ($nodes as $node) {
                $pteroNode = $this->pterodactyl->getNode($node->id);
                if ($product->memory > ($pteroNode['memory'] * ($pteroNode['memory_overallocate'] + 100) / 100) - $pteroNode['allocated_resources']['memory'] || $product->disk > ($pteroNode['disk'] * ($pteroNode['disk_overallocate'] + 100) / 100) - $pteroNode['allocated_resources']['disk']) {
                    $product->doesNotFit = true;
                }
            }
        }

        return $products;
    }
}
