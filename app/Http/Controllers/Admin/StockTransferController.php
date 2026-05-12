<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Services\StockService;
use Flasher\Prime\FlasherInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class StockTransferController extends Controller
{
    public function __construct(protected StockService $stockService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('StockTransfers/Index', [
            'ajax_url' => route('admin.stock-transfers.datatable'),
        ]);
    }

    public function datatable()
    {
        $query = StockTransfer::query()
            ->select(['id', 'reference_number', 'from_branch_id', 'to_branch_id', 'transfer_date',
                'status', 'created_at'])
            ->with(['fromBranch:id,name', 'toBranch:id,name']);

        return DataTables::eloquent($query)
            ->addColumn('from_branch_name', fn (StockTransfer $t) => optional($t->fromBranch)->name)
            ->addColumn('to_branch_name', fn (StockTransfer $t) => optional($t->toBranch)->name)
            ->addColumn('actions', fn (StockTransfer $t) => [
                'edit_url' => route('admin.stock-transfers.edit', $t),
                'delete_url' => route('admin.stock-transfers.destroy', $t),
            ])
            ->editColumn('transfer_date', fn (StockTransfer $t) => $t->transfer_date?->format('Y-m-d'))
            ->editColumn('created_at', fn (StockTransfer $t) => $t->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('StockTransfers/Form', [
            'transfer' => null,
            'options' => $this->options(),
        ]);
    }

    public function store(Request $request, FlasherInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request);
        if ($data['from_branch_id'] === $data['to_branch_id']) {
            throw ValidationException::withMessages([
                'to_branch_id' => __('Source and destination branches must differ'),
            ]);
        }

        DB::transaction(function () use ($data, $request) {
            $transfer = StockTransfer::create([
                'reference_number' => $data['reference_number'] ?? 'TR-'.now()->format('YmdHis'),
                'from_branch_id' => $data['from_branch_id'],
                'to_branch_id' => $data['to_branch_id'],
                'user_id' => $request->user()?->id,
                'transfer_date' => $data['transfer_date'],
                'status' => $data['status'] ?? 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            if ($this->affectsStock($transfer->status)) {
                $this->applyStock($transfer, $data['items']);
            }
        });

        $flasher->success(__('Stock transfer created successfully'));

        return redirect()->route('admin.stock-transfers.index');
    }

    public function edit(StockTransfer $stockTransfer): Response
    {
        $stockTransfer->load('items');

        return Inertia::render('StockTransfers/Form', [
            'transfer' => $stockTransfer,
            'options' => $this->options(),
        ]);
    }

    public function update(Request $request, StockTransfer $stockTransfer, FlasherInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request);
        $stockTransfer->loadMissing('items');

        DB::transaction(function () use ($stockTransfer, $data, $request) {
            if ($this->affectsStock($stockTransfer->status)) {
                $this->reverseStock($stockTransfer);
            }

            $stockTransfer->items()->delete();

            $stockTransfer->update([
                'from_branch_id' => $data['from_branch_id'],
                'to_branch_id' => $data['to_branch_id'],
                'transfer_date' => $data['transfer_date'],
                'status' => $data['status'] ?? $stockTransfer->status,
                'notes' => $data['notes'] ?? null,
                'user_id' => $request->user()?->id ?? $stockTransfer->user_id,
            ]);

            foreach ($data['items'] as $item) {
                StockTransferItem::create([
                    'stock_transfer_id' => $stockTransfer->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            if ($this->affectsStock($stockTransfer->status)) {
                $this->applyStock($stockTransfer, $data['items']);
            }
        });

        $flasher->success(__('Stock transfer updated successfully'));

        return redirect()->route('admin.stock-transfers.index');
    }

    public function destroy(StockTransfer $stockTransfer, FlasherInterface $flasher): RedirectResponse
    {
        $stockTransfer->loadMissing('items');

        DB::transaction(function () use ($stockTransfer) {
            if ($this->affectsStock($stockTransfer->status)) {
                $this->reverseStock($stockTransfer);
            }

            $stockTransfer->delete();
        });

        $flasher->success(__('Stock transfer deleted successfully'));

        return back();
    }

    protected function affectsStock(string $status): bool
    {
        return $status === 'completed';
    }

    protected function applyStock(StockTransfer $stockTransfer, array $items): void
    {
        foreach ($items as $item) {
            $this->stockService->decrement($item['product_id'], $stockTransfer->from_branch_id, (float) $item['quantity']);
            $this->stockService->increment($item['product_id'], $stockTransfer->to_branch_id, (float) $item['quantity']);
        }
    }

    protected function reverseStock(StockTransfer $stockTransfer): void
    {
        foreach ($stockTransfer->items as $item) {
            $this->stockService->increment($item->product_id, $stockTransfer->from_branch_id, (float) $item->quantity);
            $this->stockService->decrement($item->product_id, $stockTransfer->to_branch_id, (float) $item->quantity);
        }
    }

    protected function options(): array
    {
        return [
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'sku', 'name']),
            'statuses' => ['draft', 'pending', 'completed', 'cancelled'],
        ];
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'reference_number' => ['nullable', 'string', 'max:64'],
            'from_branch_id' => ['required', 'exists:branches,id'],
            'to_branch_id' => ['required', 'exists:branches,id'],
            'transfer_date' => ['required', 'date'],
            'status' => ['nullable', 'in:draft,pending,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
        ]);
    }
}
