<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiErrorCode;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Products\CreateProductRequest;
use App\Http\Requests\Api\Products\UpdateProductRequest;
use App\Services\ApiResponseService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedSort;

class ProductController extends Controller
{
    const ALLOWED_INCLUDES = ['servers.user', 'eggs.nest', 'nodes.location'];
    const ALLOWED_FILTERS = ['name', 'description', 'price'];
    const ALLOWED_SORTS = ['id', 'name', 'price', 'created_at', 'updated_at'];

    /**
     * Show a list of products.
     * 
     * @param Request $request
     * @return ProductResource
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 50), 100);

        $products = QueryBuilder::for(Product::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters(self::ALLOWED_FILTERS)
            ->allowedSorts(self::ALLOWED_SORTS)
            ->paginate($perPage);

        return ApiResponseService::success(
            ProductResource::collection($products)->toArray($request),
            [
                'current_page' => $products->currentPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ]
        );
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

        return ApiResponseService::created(ProductResource::make($product->refresh())->toArray($request));
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

        return ApiResponseService::success(ProductResource::make($product)->toArray($request));
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

        return ApiResponseService::success(ProductResource::make($product->fresh())->toArray($request));
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
            return ApiResponseService::error(
                ApiErrorCode::VALIDATION_ERROR,
                'Cannot delete product with associated servers.',
                422,
                ['servers_count' => $product->servers()->count()]
            );
        }

        $product->delete();

        return ApiResponseService::noContent();
    }
}