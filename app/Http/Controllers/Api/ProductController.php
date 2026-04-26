<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Products\CreateProductRequest;
use App\Http\Requests\Api\Products\UpdateProductRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Product Management
 */

class ProductController extends Controller
{
    const ALLOWED_INCLUDES = ['servers.user', 'eggs.nest', 'nodes.location'];
    const ALLOWED_FILTERS = ['name', 'description', 'price'];

    /**
     * Show a list of products.
     *
     * @response {
     *  "data": [
     *    {
     *      "id": "F6QnQ-91Afeo0chihMss",
     *      "name": "Basic Server",
     *      "description": "Perfect for small communities",
     *      "price": "5.00",
     *      "memory": 2048,
     *      "cpu": 100,
     *      "swap": 0,
     *      "disk": 10240,
     *      "io": 500,
     *      "databases": 1,
     *      "backups": 1,
     *      "serverlimit": 1,
     *      "allocations": 1,
     *      "oom_killer": true,
     *      "default_billing_priority": 0,
     *      "disabled": false,
     *      "minimum_credits": "5.00",
     *      "billing_period": "monthly",
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *    }
     *  ],
     *  "meta": { "total": 1 }
     * }
     *
     * @param Request $request
     * @return ProductResource
     */
    public function index(Request $request)
    {
        $products = QueryBuilder::for(Product::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters(self::ALLOWED_FILTERS)
            ->paginate($request->input('per_page') ?? 50);

        return ProductResource::collection($products);
    }

    /**
     * Store a new product in the system.
     *
     * @bodyParam name string required The name of the product. Example: Medium Minecraft
     * @bodyParam description string The description of the product. Example: Perfect for your SMP with friends
     * @bodyParam price number required The price of the product. Example: 10.00
     * @bodyParam memory integer required The memory in MB. Example: 4096
     * @bodyParam cpu integer required The CPU limit in %. Example: 200
     * @bodyParam swap integer required The swap limit in MB. Example: 0
     * @bodyParam disk integer required The disk limit in MB. Example: 20480
     * @bodyParam io integer required The IO limit. Example: 500
     * @bodyParam serverlimit integer required The server limit. Example: 1
     * @bodyParam databases integer required The database limit. Example: 3
     * @bodyParam backups integer required The backup limit. Example: 2
     * @bodyParam allocations integer required The allocation limit. Example: 1
     * @bodyParam billing_period string required The billing period. Example: monthly
     *
     * @response {
     *  "data": {
     *      "id": "vY8xK9pZ",
     *      "name": "Medium Minecraft",
     *      "description": "Perfect for your SMP with friends",
     *      "price": "10.00",
     *      "memory": 4096,
     *      "cpu": 200,
     *      "swap": 0,
     *      "disk": 20480,
     *      "io": 500,
     *      "databases": 3,
     *      "backups": 2,
     *      "serverlimit": 1,
     *      "allocations": 1,
     *      "oom_killer": true,
     *      "default_billing_priority": 0,
     *      "disabled": false,
     *      "minimum_credits": "10.00",
     *      "billing_period": "monthly",
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *  }
     * }
     *
     * @param CreateProductRequest $request
     * @return ProductResource
     */
    public function store(CreateProductRequest $request)
    {
        $data = $request->validated();

        $product = Product::create($data);

        if (isset($data['eggs'])) {
            $product->eggs()->sync($data['eggs']);
        }

        if (isset($data['nodes'])) {
            $product->nodes()->sync($data['nodes']);
        }

        $product->load(['eggs', 'nodes']);

        return ProductResource::make($product->refresh());
    }

    /**
     * Show the specified product.
     *
     * @response {
     *  "data": {
     *      "id": "vY8xK9pZ",
     *      "name": "Medium Minecraft",
     *      "description": "Perfect for your SMP with friends",
     *      "price": "10.00",
     *      "memory": 4096,
     *      "cpu": 200,
     *      "swap": 0,
     *      "disk": 20480,
     *      "io": 500,
     *      "databases": 3,
     *      "backups": 2,
     *      "serverlimit": 1,
     *      "allocations": 1,
     *      "oom_killer": true,
     *      "default_billing_priority": 0,
     *      "disabled": false,
     *      "minimum_credits": "10.00",
     *      "billing_period": "monthly",
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *  }
     * }
     *
     * @queryParam include string Comma-separated list of related resources to include. Example: servers.user,eggs.nest,nodes.location
     *
     * @param Request $request
     * @param Product $product
     * @return ProductResource
     *
     * @throws ModelNotFoundException
     */
    public function show(Request $request, Product $product)
    {
        return ProductResource::make($product);
    }

    /**
     * Update the specified product in the system.
     *
     * @bodyParam name string The name of the product. Example: Medium Minecraft
     * @bodyParam description string The description of the product. Example: Perfect for your SMP with friends
     * @bodyParam price number The price of the product. Example: 10.00
     * @bodyParam memory integer The memory in MB. Example: 4096
     * @bodyParam cpu integer The CPU limit in %. Example: 200
     * @bodyParam swap integer The swap limit in MB. Example: 0
     * @bodyParam disk integer The disk limit in MB. Example: 20480
     * @bodyParam io integer The IO limit. Example: 500
     * @bodyParam serverlimit integer The server limit. Example: 1
     * @bodyParam databases integer The database limit. Example: 3
     * @bodyParam backups integer The backup limit. Example: 2
     * @bodyParam allocations integer The allocation limit. Example: 1
     * @bodyParam billing_period string The billing period. Example: monthly
     *
     * @response {
     *  "data": {
     *      "id": "vY8xK9pZ",
     *      "name": "Premium Ryzen Plan",
     *      "description": "High-performance Ryzen 9 7950X based hosting",
     *      "price": "10.00",
     *      "memory": 4096,
     *      "cpu": 200,
     *      "swap": 0,
     *      "disk": 20480,
     *      "io": 500,
     *      "databases": 3,
     *      "backups": 2,
     *      "serverlimit": 1,
     *      "allocations": 1,
     *      "oom_killer": true,
     *      "default_billing_priority": 0,
     *      "disabled": false,
     *      "minimum_credits": "10.00",
     *      "billing_period": "monthly",
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *  }
     * }
     *
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return ProductResource
     *
     * @throws ModelNotFoundException
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        $product->update($data);

        if (isset($data['eggs'])) {
            $product->eggs()->sync($data['eggs']);
        }

        if (isset($data['nodes'])) {
            $product->nodes()->sync($data['nodes']);
        }

        $product->load(['eggs', 'nodes']);

        return ProductResource::make($product);
    }

    /**
     * Remove the specified product from the system.
     *
     * @response 204 {}
     *
     * @param Request $request
     * @param Product $product
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws ModelNotFoundException
     */
    public function destroy(Request $request, Product $product)
    {
        if ($product->servers()->exists()) {
            return response()->json([
                'message' => 'Cannot delete product with associated servers.',
                'meta' => [
                    'servers_count' => $product->servers()->count(),
                ]
            ], 422);
        }

        $product->delete();

        return response()->noContent();
    }
}
