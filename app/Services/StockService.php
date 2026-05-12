<?php

namespace App\Services;

use App\Models\ProductStock;

class StockService
{
    public function increment(int $productId, int $branchId, float $quantity, ?float $costPrice = null): ProductStock
    {
        $stock = ProductStock::firstOrCreate(
            ['product_id' => $productId, 'branch_id' => $branchId],
            ['quantity' => 0, 'avg_cost' => 0]
        );

        if ($costPrice !== null && $quantity > 0) {
            $existingValue = (float) $stock->quantity * (float) $stock->avg_cost;
            $newValue = $existingValue + ($costPrice * $quantity);
            $newQty = (float) $stock->quantity + $quantity;
            $stock->avg_cost = $newQty > 0 ? $newValue / $newQty : $costPrice;
        }

        $stock->quantity = (float) $stock->quantity + $quantity;
        $stock->save();

        return $stock;
    }

    public function decrement(int $productId, int $branchId, float $quantity): ProductStock
    {
        $stock = ProductStock::firstOrCreate(
            ['product_id' => $productId, 'branch_id' => $branchId],
            ['quantity' => 0, 'avg_cost' => 0]
        );

        $stock->quantity = max(0, (float) $stock->quantity - $quantity);
        $stock->save();

        return $stock;
    }
}
