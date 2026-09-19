<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController
{
    public function index(Request $request)
    {
        return response()->json(
            Sale::where('business_id', $request->user()->business_id)
                ->with('items.product')
                ->orderByDesc('sold_at')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sold_at' => 'required|date',
            'notes' => 'nullable|string',
            'shift_id' => 'nullable|exists:shifts,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $sale = DB::transaction(function () use ($request, $data) {
            $business = $request->user()->business;
            $sale = Sale::create([
                'business_id' => $business->id,
                'shift_id' => $data['shift_id'] ?? null,
                'user_id' => $request->user()->id,
                'sale_no' => 'SALE-' . now()->timestamp,
                'sold_at' => $data['sold_at'],
                'notes' => $data['notes'] ?? null,
                'total_amount' => 0,
            ]);

            $total = 0;
            foreach ($data['items'] as $item) {
                $product = Product::where('business_id', $business->id)->findOrFail($item['product_id']);

                if ($product->stock_quantity < $item['quantity']) {
                    throw new \InvalidArgumentException('Insufficient stock for product: ' . $product->name);
                }

                $lineTotal = $item['quantity'] * $item['unit_price'];

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'unit_cost_snapshot' => $product->default_cost,
                    'line_total' => $lineTotal,
                ]);

                $product->stock_quantity = (int) $product->stock_quantity - (int) $item['quantity'];
                $product->save();

                $total += $lineTotal;
            }

            $sale->total_amount = $total;
            $sale->save();

            return $sale->load('items.product');
        });

        return response()->json(['data' => $sale], 201);
    }

    public function show(Request $request, Sale $sale)
    {
        if ((int) $sale->business_id !== (int) $request->user()->business_id) {
            return response()->json(['message' => 'You do not have access to this business.'], 403);
        }

        return response()->json($sale->load('items.product'));
    }

    public function update(Request $request, Sale $sale)
    {
        if ((int) $sale->business_id !== (int) $request->user()->business_id) {
            return response()->json(['message' => 'You do not have access to this business.'], 403);
        }

        $data = $request->validate([
            'sold_at' => 'sometimes|date',
            'notes' => 'nullable|string',
        ]);

        $sale->update($data);

        return response()->json($sale->fresh());
    }

    public function destroy(Request $request, Sale $sale)
    {
        if ((int) $sale->business_id !== (int) $request->user()->business_id) {
            return response()->json(['message' => 'You do not have access to this business.'], 403);
        }

        $sale->delete();

        return response()->json(['message' => 'Sale deleted successfully']);
    }
}
