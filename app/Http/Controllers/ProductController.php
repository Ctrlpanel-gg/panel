<?php

namespace App\Http\Controllers;

use App\Models\Egg;
use App\Models\Location;
use App\Models\Node;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductController extends Controller
{
    /**
     * @description get product locations based on selected egg
     * @param Request $request
     * @param Egg $egg
     * @return Collection|JsonResponse
     */
    public function getNodesBasedOnEgg(Request $request, Egg $egg)
    {
        if (is_null($egg->id)) return response()->json('Egg ID is required', '400');

        #get products that include this egg
        $products = Product::query()->with('nodes')->whereHas('eggs', function (Builder $builder) use ($egg) {
            $builder->where('id', '=', $egg->id);
        })->get();

        $nodes = collect();

        #filter unique nodes
        $products->each(function (Product $product) use ($nodes) {
            $product->nodes->each(function (Node $node) use ($nodes) {
                if (!$nodes->contains('id', $node->id) && !$node->disabled) {
                    $nodes->add($node);
                }
            });
        });

        return $nodes;
    }

    /**
     * @description get product locations based on selected egg
     * @param Request $request
     * @param Egg $egg
     * @return Collection|JsonResponse
     */
    public function getLocationsBasedOnEgg(Request $request, Egg $egg)
    {
        $nodes = $this->getNodesBasedOnEgg($request, $egg);
        $locations = collect();

        //locations
        $nodes->each(function (Node $node) use ($nodes, $locations) {
            /** @var Location $location */
            $location = $node->location;

            if (!$locations->contains('id', $location->id)) {
                $nodeIds = $nodes->map(function ($node) {
                    return $node->id;
                });

                $location->nodes = $location->nodes()
                    ->whereIn('id', $nodeIds)
                    ->get();

                $locations->add($location);
            }
        });

        return $locations;
    }

    /**
     * @param Node $node
     * @param Egg $egg
     * @return Collection|JsonResponse
     */
    public function getProductsBasedOnNode(Egg $egg, Node $node)
    {
        if (is_null($egg->id) || is_null($node->id)) return response()->json('node and egg id is required', '400');

        return Product::query()
            ->whereHas('nodes', function (Builder $builder) use ($node) {
                $builder->where('id', '=', $node->id);
            })
            ->whereHas('eggs', function (Builder $builder) use ($egg) {
                $builder->where('id', '=', $egg->id);
            })
            ->get();
    }
}
