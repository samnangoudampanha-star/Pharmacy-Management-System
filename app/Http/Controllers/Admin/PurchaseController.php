<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Services\StockService;
use Flasher\SweetAlert\Prime\SweetAlertInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class PurchaseController extends Controller
{
    public function __construct(protected StockService $stockService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Purchases/Index', [
            'ajax_url' => route('admin.purchases.datatable'),
        ]);
    }

    public function datatable()
    {
        $query = Purchase::query()
            ->select(['id', 'reference_number', 'branch_id', 'supplier_id', 'purchase_date',
                'subtotal', 'tax', 'discount', 'total', 'paid', 'due', 'status', 'created_at'])
            ->with(['branch:id,name', 'supplier:id,name']);

        return DataTables::eloquent($query)
            ->addColumn('branch_name', fn (Purchase $p) => optional($p->branch)->name)
            ->addColumn('supplier_name', fn (Purchase $p) => optional($p->supplier)->name)
            ->addColumn('actions', fn (Purchase $p) => [
                'edit_url' => route('admin.purchases.edit', $p),
                'delete_url' => route('admin.purchases.destroy', $p),
            ])
            ->editColumn('purchase_date', fn (Purchase $p) => $p->purchase_date?->format('Y-m-d'))
            ->editColumn('created_at', fn (Purchase $p) => $p->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Purchases/Form', [
            'purchase' => null,
            'options' => $this->options(),
        ]);
    }

    public function store(Request $request, SweetAlertInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $request) {
            $purchase = Purchase::create([
                'reference_number' => $data['reference_number'] ?? 'PO-'.now()->format('YmdHis'),
                'branch_id' => $data['branch_id'],
                'supplier_id' => $data['supplier_id'],
                'user_id' => $request->user()?->id,
                'purchase_date' => $data['purchase_date'],
                'status' => $data['status'] ?? 'received',
                'notes' => $data['notes'] ?? null,
                'subtotal' => 0, 'tax' => 0, 'discount' => 0, 'total' => 0, 'paid' => 0, 'due' => 0,
            ]);

            $this->syncItems($purchase, $data['items']);
        });

        $flasher->addSuccess(__('Purchase created successfully'));

        return redirect()->route('admin.purchases.index');
    }

    public function edit(Purchase $purchase): Response
    {
        $purchase->load('items');

        return Inertia::render('Purchases/Form', [
            'purchase' => $purchase,
            'options' => $this->options(),
        ]);
    }

    public function update(Request $request, Purchase $purchase, SweetAlertInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($purchase, $data, $request) {
            // Reverse stock for existing items, then re-apply with new lines.
            foreach ($purchase->items as $existing) {
                $this->stockService->decrement(
                    $existing->product_id,
                    $purchase->branch_id,
                    (float) $existing->quantity
                );
            }
            $purchase->items()->delete();

            $purchase->update([
                'branch_id' => $data['branch_id'],
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'status' => $data['status'] ?? $purchase->status,
                'notes' => $data['notes'] ?? null,
                'user_id' => $request->user()?->id ?? $purchase->user_id,
            ]);

            $this->syncItems($purchase, $data['items']);
        });

        $flasher->addSuccess(__('Purchase updated successfully'));

        return redirect()->route('admin.purchases.index');
    }

    public function destroy(Purchase $purchase, SweetAlertInterface $flasher): RedirectResponse
    {
        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                $this->stockService->decrement($item->product_id, $purchase->branch_id, (float) $item->quantity);
            }
            $purchase->delete();
        });

        $flasher->addSuccess(__('Purchase deleted successfully'));

        return back();
    }

    protected function syncItems(Purchase $purchase, array $items): void
    {
        $subtotal = 0;
        $tax = 0;
        $discount = 0;

        foreach ($items as $itemData) {
            $lineQty = (float) $itemData['quantity'];
            $lineCost = (float) $itemData['cost_price'];
            $lineTaxRate = (float) ($itemData['tax_rate'] ?? 0);
            $lineDiscount = (float) ($itemData['discount'] ?? 0);
            $lineSubtotal = ($lineQty * $lineCost) - $lineDiscount;
            $lineTax = $lineSubtotal * ($lineTaxRate / 100);

            $subtotal += $lineSubtotal;
            $tax += $lineTax;
            $discount += $lineDiscount;

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $lineQty,
                'cost_price' => $lineCost,
                'tax_rate' => $lineTaxRate,
                'discount' => $lineDiscount,
                'subtotal' => $lineSubtotal + $lineTax,
                'expiry_date' => $itemData['expiry_date'] ?? null,
                'batch_number' => $itemData['batch_number'] ?? null,
            ]);

            $this->stockService->increment($itemData['product_id'], $purchase->branch_id, $lineQty, $lineCost);
        }

        $total = $subtotal + $tax;
        $purchase->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
            'paid' => 0,
            'due' => $total,
        ]);
    }

    protected function options(): array
    {
        return [
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'sku', 'name', 'cost_price', 'tax_rate']),
        ];
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'reference_number' => ['nullable', 'string', 'max:64'],
            'branch_id' => ['required', 'exists:branches,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'status' => ['nullable', 'in:draft,received,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.cost_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.batch_number' => ['nullable', 'string', 'max:64'],
        ]);
    }
}
