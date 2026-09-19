<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function recordMovement(int $businessId, Product $product, ?int $shiftId, string $movementType, string $referenceType, int $referenceId, int $quantityIn, int $quantityOut, float $unitCost = 0, ?int $createdBy = null): void
    {
        StockMovement::create([
            'business_id' => $businessId,
            'product_id' => $product->id,
            'shift_id' => $shiftId,
            'movement_type' => $movementType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'quantity_in' => $quantityIn,
            'quantity_out' => $quantityOut,
            'unit_cost' => $unitCost,
            'occurred_at' => now(),
            'created_by' => $createdBy,
        ]);
    }

    public function ensureStock(Product $product, int $quantity): void
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative.');
        }

        if ($product->stock_quantity < $quantity) {
            throw new \InvalidArgumentException('Insufficient stock for product: ' . $product->name);
        }
    }

    public function addStock(Product $product, int $quantity): void
    {
        $product->stock_quantity = max(0, (int) $product->stock_quantity + $quantity);
        $product->save();
    }

    public function removeStock(Product $product, int $quantity): void
    {
        if ($quantity > $product->stock_quantity) {
            throw new \InvalidArgumentException('Insufficient stock for product: ' . $product->name);
        }

        $product->stock_quantity = max(0, (int) $product->stock_quantity - $quantity);
        $product->save();
    }
}
