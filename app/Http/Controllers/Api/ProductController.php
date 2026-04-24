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

class ProductController extends Controller
{
    const ALLOWED_INCLUDES = ['servers.user', 'eggs.nest', 'nodes.location'];
    const ALLOWED_FILTERS = ['name', 'description', 'price'];

    /**
     * Show a list of products.
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