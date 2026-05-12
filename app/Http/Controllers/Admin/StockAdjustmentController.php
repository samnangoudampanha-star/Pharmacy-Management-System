<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Services\StockService;
use Flasher\SweetAlert\Prime\SweetAlertInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class StockAdjustmentController extends Controller
{
    public function __construct(protected StockService $stockService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('StockAdjustments/Index', [
            'ajax_url' => route('admin.stock-adjustments.datatable'),
        ]);
    }

    public function datatable()
    {
        $query = StockAdjustment::query()
            ->select(['id', 'reference_number', 'branch_id', 'product_id', 'adjustment_date',
                'quantity', 'type', 'reason', 'created_at'])
            ->with(['branch:id,name', 'product:id,sku,name']);

        return DataTables::eloquent($query)
            ->addColumn('branch_name', fn (StockAdjustment $a) => optional($a->branch)->name)
            ->addColumn('product_name', fn (StockAdjustment $a) => optional($a->product)->name)
            ->addColumn('actions', fn (StockAdjustment $a) => [
                'edit_url' => route('admin.stock-adjustments.edit', $a),
                'delete_url' => route('admin.stock-adjustments.destroy', $a),
            ])
            ->editColumn('adjustment_date', fn (StockAdjustment $a) => $a->adjustment_date?->format('Y-m-d'))
            ->editColumn('created_at', fn (StockAdjustment $a) => $a->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('StockAdjustments/Form', [
            'adjustment' => null,
            'options' => $this->options(),
        ]);
    }

    public function store(Request $request, SweetAlertInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $request) {
            $adj = StockAdjustment::create([
                'reference_number' => $data['reference_number'] ?? 'ADJ-'.now()->format('YmdHis'),
                'branch_id' => $data['branch_id'],
                'product_id' => $data['product_id'],
                'user_id' => $request->user()?->id,
                'adjustment_date' => $data['adjustment_date'],
                'quantity' => $data['quantity'],
                'type' => $data['type'],
                'reason' => $data['reason'] ?? null,
            ]);

            if ($adj->type === 'increase') {
                $this->stockService->increment($adj->product_id, $adj->branch_id, (float) $adj->quantity);
            } else {
                $this->stockService->decrement($adj->product_id, $adj->branch_id, (float) $adj->quantity);
            }
        });

        $flasher->addSuccess(__('Stock adjustment recorded'));

        return redirect()->route('admin.stock-adjustments.index');
    }

    public function edit(StockAdjustment $stockAdjustment): Response
    {
        return Inertia::render('StockAdjustments/Form', [
            'adjustment' => $stockAdjustment,
            'options' => $this->options(),
        ]);
    }

    public function update(Request $request, StockAdjustment $stockAdjustment, SweetAlertInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($stockAdjustment, $data, $request) {
            // Reverse old effect.
            if ($stockAdjustment->type === 'increase') {
                $this->stockService->decrement($stockAdjustment->product_id, $stockAdjustment->branch_id, (float) $stockAdjustment->quantity);
            } else {
                $this->stockService->increment($stockAdjustment->product_id, $stockAdjustment->branch_id, (float) $stockAdjustment->quantity);
            }

            $stockAdjustment->update([
                'branch_id' => $data['branch_id'],
                'product_id' => $data['product_id'],
                'adjustment_date' => $data['adjustment_date'],
                'quantity' => $data['quantity'],
                'type' => $data['type'],
                'reason' => $data['reason'] ?? null,
                'user_id' => $request->user()?->id ?? $stockAdjustment->user_id,
            ]);

            if ($stockAdjustment->type === 'increase') {
                $this->stockService->increment($stockAdjustment->product_id, $stockAdjustment->branch_id, (float) $stockAdjustment->quantity);
            } else {
                $this->stockService->decrement($stockAdjustment->product_id, $stockAdjustment->branch_id, (float) $stockAdjustment->quantity);
            }
        });

        $flasher->addSuccess(__('Stock adjustment updated'));

        return redirect()->route('admin.stock-adjustments.index');
    }

    public function destroy(StockAdjustment $stockAdjustment, SweetAlertInterface $flasher): RedirectResponse
    {
        DB::transaction(function () use ($stockAdjustment) {
            if ($stockAdjustment->type === 'increase') {
                $this->stockService->decrement($stockAdjustment->product_id, $stockAdjustment->branch_id, (float) $stockAdjustment->quantity);
            } else {
                $this->stockService->increment($stockAdjustment->product_id, $stockAdjustment->branch_id, (float) $stockAdjustment->quantity);
            }
            $stockAdjustment->delete();
        });

        $flasher->addSuccess(__('Stock adjustment removed'));

        return back();
    }

    protected function options(): array
    {
        return [
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'sku', 'name']),
            'types' => ['increase', 'decrease'],
        ];
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'reference_number' => ['nullable', 'string', 'max:64'],
            'branch_id' => ['required', 'exists:branches,id'],
            'product_id' => ['required', 'exists:products,id'],
            'adjustment_date' => ['required', 'date'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'type' => ['required', 'in:increase,decrease'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
