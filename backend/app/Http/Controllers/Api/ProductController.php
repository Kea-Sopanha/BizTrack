<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController
{
    public function index(Request $request)
    {
        return response()->json(
            Product::where('business_id', $request->user()->business_id)
                ->orderBy('name')
                ->get()
        );
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'owner') {
            return response()->json(['message' => 'Owner access only.'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string',
            'sku' => 'nullable|string',
            'category_id' => 'nullable|exists:product_categories,id',
            'unit' => 'nullable|string',
            'selling_price' => 'numeric|min:0',
            'default_cost' => 'numeric|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $product = Product::create([
            ...$data,
            'business_id' => $request->user()->business_id,
            'stock_quantity' => (int) ($data['stock_quantity'] ?? 0),
        ]);

        return response()->json($product, 201);
    }

    public function show(Request $request, Product $product)
    {
        if ((int) $product->business_id !== (int) $request->user()->business_id) {
            return response()->json(['message' => 'You do not have access to this business.'], 403);
        }

        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        if ((int) $product->business_id !== (int) $request->user()->business_id) {
            return response()->json(['message' => 'You do not have access to this business.'], 403);
        }

        if ($request->user()->role !== 'owner') {
            return response()->json(['message' => 'Owner access only.'], 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string',
            'sku' => 'nullable|string',
            'category_id' => 'nullable|exists:product_categories,id',
            'unit' => 'nullable|string',
            'selling_price' => 'sometimes|numeric|min:0',
            'default_cost' => 'sometimes|numeric|min:0',
            'minimum_stock' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $product->update($data);

        return response()->json($product);
    }

    public function destroy(Request $request, Product $product)
    {
        if ((int) $product->business_id !== (int) $request->user()->business_id) {
            return response()->json(['message' => 'You do not have access to this business.'], 403);
        }

        if ($request->user()->role !== 'owner') {
            return response()->json(['message' => 'Owner access only.'], 403);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
