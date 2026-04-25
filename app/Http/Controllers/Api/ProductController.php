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
     *      "id": 1,
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
     *      "created_at": "2023-01-01 00:00:00",
     *      "updated_at": "2023-01-01 00:00:00"
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
     * @response {
     *  "data": {
     *      "id": 1,
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
     *      "created_at": "2023-01-01 00:00:00",
     *      "updated_at": "2023-01-01 00:00:00"
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
     * @urlParam product integer required The ID of the product. Example: 1
     * 
     * @response {
     *  "data": {
     *      "id": 1,
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
     *      "created_at": "2023-01-01 00:00:00",
     *      "updated_at": "2023-01-01 00:00:00"
     *  }
     * }
     * 
     * @queryParam include string Comma-separated list of related resources to include. Example: servers.user,eggs.nest,nodes.location
     * 
     * @param Request $request
     * @param string $productId
     * @return ProductResource
     * 
     * @throws ModelNotFoundException
     */
    public function show(Request $request, string $productId)
    {
        $product = QueryBuilder::for(Product::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->where('id', $productId)
            ->firstOrFail();

        return ProductResource::make($product);
    }

    /**
     * Update the specified product in the system.
     * 
     * @urlParam product integer required The ID of the product. Example: 1
     * 
     * @response {
     *  "data": {
     *      "id": 1,
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
     *      "created_at": "2023-01-01 00:00:00",
     *      "updated_at": "2023-01-01 00:00:00"
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
     * @urlParam product integer required The ID of the product. Example: 1
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
