<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'total_branches' => Branch::count(),
                'total_products' => Product::count(),
                'today_sales' => (float) Sale::whereDate('sale_date', today())->sum('total'),
                'today_purchases' => (float) Purchase::whereDate('purchase_date', today())->sum('total'),
                'low_stock' => Product::where('reorder_level', '>', 0)
                    ->whereDoesntHave('stocks', function ($q) {
                        $q->whereColumn('quantity', '>', 'products.reorder_level');
                    })
                    ->count(),
            ],
            'recent_sales' => Sale::with(['customer:id,name', 'branch:id,name'])
                ->latest()->limit(5)->get(),
            'recent_purchases' => Purchase::with(['supplier:id,name', 'branch:id,name'])
                ->latest()->limit(5)->get(),
            'sales_by_branch' => Sale::query()
                ->select('branch_id', DB::raw('COALESCE(SUM(total), 0) as total'))
                ->groupBy('branch_id')
                ->with('branch:id,name')
                ->get(),
        ]);
    }
}
