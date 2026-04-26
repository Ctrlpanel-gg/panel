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
     * List all products
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
     * Create product
     *
     * @bodyParam name string required Max 30 chars. Example: Medium Minecraft
     * @bodyParam description string required Max 191 chars. Example: Perfect for your SMP with friends
     * @bodyParam price number required Min 0. Example: 10.00
     * @bodyParam minimum_credits number Min 0, must be GTE price. Example: 10.00
     * @bodyParam memory integer required Memory in MB. Use 0 for unlimited. Example: 4096
     * @bodyParam cpu integer required CPU limit in %. Use 0 for unlimited. Example: 200
     * @bodyParam swap integer required Swap in MB. Use 0 to disable, -1 for unlimited. Example: 0
     * @bodyParam disk integer required Disk in MB. Use 0 for unlimited. Example: 20480
     * @bodyParam io integer required IO limit. Example: 500
     * @bodyParam serverlimit integer required Max servers. Use 0 for unlimited. Example: 1
     * @bodyParam databases integer required Max databases. Example: 3
     * @bodyParam backups integer required Max backups. Example: 2
     * @bodyParam allocations integer required Max allocations. Example: 1
     * @bodyParam billing_period string required hourly, daily, weekly, monthly, quarterly, half-annually, annually. Example: monthly
     * @bodyParam nodes integer[] List of node IDs associated with the product. Example: [1, 2]
     * @bodyParam eggs integer[] List of egg IDs associated with the product. Example: [1, 3]
     * @bodyParam disabled boolean Whether the product is disabled. Example: false
     * @bodyParam oom_killer boolean Whether the OOM killer is enabled. Example: true
     * @bodyParam default_billing_priority integer The default billing priority. Example: 0
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
     * Get product details
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
     * Update product
     *
     * @bodyParam name string The name of the product. Example: Medium Minecraft
     * @bodyParam description string The description of the product. Example: Perfect for your SMP with friends
     * @bodyParam price number The price of the product. Example: 10.00
     * @bodyParam minimum_credits number The minimum credits required to purchase. Example: 10.00
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
     * @bodyParam nodes integer[] List of node IDs associated with the product. Example: [1, 2]
     * @bodyParam eggs integer[] List of egg IDs associated with the product. Example: [1, 3]
     * @bodyParam disabled boolean Whether the product is disabled. Example: false
     * @bodyParam oom_killer boolean Whether the OOM killer is enabled. Example: true
     * @bodyParam default_billing_priority integer The default billing priority. Example: 0
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
     * Delete product
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
