<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Model;

class PaymentService
{
    public function syncPayable(Model $payable): void
    {
        if (! $payable instanceof Sale && ! $payable instanceof Purchase) {
            return;
        }

        $paid = (float) $payable->payments()->sum('amount');
        $total = (float) $payable->total;

        $payable->update([
            'paid' => min($paid, $total),
            'due' => max(0, $total - $paid),
        ]);
    }
}
