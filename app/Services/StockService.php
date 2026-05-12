<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Validation\ValidationException;

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

        if ((float) $stock->quantity < $quantity) {
            $productName = Product::query()->whereKey($productId)->value('name') ?? __('Selected product');

            throw ValidationException::withMessages([
                'items' => __('Insufficient stock for :product in the selected branch.', ['product' => $productName]),
            ]);
        }

        $stock->quantity = (float) $stock->quantity - $quantity;
        $stock->save();

        return $stock;
    }
}
