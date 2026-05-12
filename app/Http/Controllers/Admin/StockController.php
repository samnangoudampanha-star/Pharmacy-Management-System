<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ProductStock;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class StockController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Stocks/Index', [
            'ajax_url' => route('admin.stocks.datatable'),
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function datatable()
    {
        $query = ProductStock::query()
            ->select(['product_stocks.id', 'product_stocks.product_id', 'product_stocks.branch_id',
                'product_stocks.quantity', 'product_stocks.avg_cost', 'product_stocks.updated_at'])
            ->with([
                'product:id,sku,name,reorder_level',
                'branch:id,name',
            ]);

        return DataTables::eloquent($query)
            ->addColumn('product_name', fn (ProductStock $s) => optional($s->product)->name)
            ->addColumn('product_sku', fn (ProductStock $s) => optional($s->product)->sku)
            ->addColumn('branch_name', fn (ProductStock $s) => optional($s->branch)->name)
            ->addColumn('reorder_level', fn (ProductStock $s) => optional($s->product)->reorder_level)
            ->editColumn('updated_at', fn (ProductStock $s) => $s->updated_at?->format('Y-m-d H:i'))
            ->toJson();
    }
}
