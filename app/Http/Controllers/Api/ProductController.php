<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    const ALLOWED_INCLUDES = ['servers.user', 'eggs.nest', 'nodes.location'];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = QueryBuilder::for(Product::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->paginate(request()->input('per_page') ?? 50);

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:30',
            'price' => 'required|numeric|max:1000000|min:0',
            'memory' => 'required|numeric|max:1000000|min:5',
            'cpu' => 'required|numeric|max:1000000|min:0',
            'swap' => 'required|numeric|max:1000000|min:0',
            'description' => 'required|string|max:191',
            'disk' => 'required|numeric|max:1000000|min:5',
            'minimum_credits' => 'required|numeric|max:1000000|min:-1',
            'io' => 'required|numeric|max:1000000|min:0',
            'serverlimit' => 'required|numeric|max:1000000|min:0',
            'databases' => 'required|numeric|max:1000000|min:0',
            'backups' => 'required|numeric|max:1000000|min:0',
            'allocations' => 'required|numeric|max:1000000|min:0',
            'nodes.*' => 'required|exists:nodes,id',
            'eggs.*' => 'required|exists:eggs,id',
            'disabled' => 'nullable',
            'oom_killer' => 'nullable',
            'billing_period' => 'required|in:hourly,daily,weekly,monthly,quarterly,half-annually,annually'
        ]);

        $product = Product::create($data);

        $product->eggs()->attach($data['eggs']);
        $product->nodes()->attach($data['nodes']);

        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product = QueryBuilder::for(Product::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->where('id', $product->id)
            ->firstOrFail();

        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|max:30',
            'price' => 'sometimes|required|numeric|max:1000000|min:0',
            'memory' => 'sometimes|required|numeric|max:1000000|min:5',
            'cpu' => 'sometimes|required|numeric|max:1000000|min:0',
            'swap' => 'sometimes|required|numeric|max:1000000|min:0',
            'description' => 'sometimes|required|string|max:191',
            'disk' => 'sometimes|required|numeric|max:1000000|min:5',
            'minimum_credits' => 'sometimes|required|numeric|max:1000000|min:-1',
            'io' => 'sometimes|required|numeric|max:1000000|min:0',
            'serverlimit' => 'sometimes|required|numeric|max:1000000|min:0',
            'databases' => 'sometimes|required|numeric|max:1000000|min:0',
            'backups' => 'sometimes|required|numeric|max:1000000|min:0',
            'allocations' => 'sometimes|required|numeric|max:1000000|min:0',
            'nodes.*' => 'sometimes|required|exists:nodes,id',
            'eggs.*' => 'sometimes|required|exists:eggs,id',
            'disabled' => 'sometimes|nullable',
            'oom_killer' => 'sometimes|nullable',
            'billing_period' => 'sometimes|required|in:hourly,daily,weekly,monthly,quarterly,half-annually,annually'
        ]);

        $product = Product::findOrFail($product->id);

        $product->update($data);

        if (isset($data['eggs'])) {
            $product->eggs()->sync($data['eggs']);
        }

        if (isset($data['nodes'])) {
            $product->nodes()->sync($data['nodes']);
        }

        $product->load('eggs', 'nodes');

        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->servers()->count() > 0) {
            return response()->json([
                'message' => 'Product has servers attached.'
            ], 400);
        }

        $product->delete();

        return response()->json($product);
    }
}
