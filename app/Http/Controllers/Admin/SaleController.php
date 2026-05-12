<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\PaymentService;
use App\Services\StockService;
use Flasher\Prime\FlasherInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class SaleController extends Controller
{
    public function __construct(
        protected StockService $stockService,
        protected PaymentService $paymentService,
    )
    {
    }

    public function index(): Response
    {
        return Inertia::render('Sales/Index', [
            'ajax_url' => route('admin.sales.datatable'),
        ]);
    }

    public function datatable()
    {
        $query = Sale::query()
            ->select(['id', 'invoice_number', 'branch_id', 'customer_id', 'sale_date',
                'subtotal', 'tax', 'discount', 'total', 'paid', 'due', 'payment_method', 'status', 'created_at'])
            ->with(['branch:id,name', 'customer:id,name']);

        return DataTables::eloquent($query)
            ->addColumn('branch_name', fn (Sale $s) => optional($s->branch)->name)
            ->addColumn('customer_name', fn (Sale $s) => optional($s->customer)->name ?: __('Walk-in'))
            ->addColumn('actions', fn (Sale $s) => [
                'edit_url' => route('admin.sales.edit', $s),
                'delete_url' => route('admin.sales.destroy', $s),
            ])
            ->editColumn('sale_date', fn (Sale $s) => $s->sale_date?->format('Y-m-d'))
            ->editColumn('created_at', fn (Sale $s) => $s->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Sales/Form', [
            'sale' => null,
            'options' => $this->options(),
        ]);
    }

    public function store(Request $request, FlasherInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $request) {
            $sale = Sale::create([
                'invoice_number' => $data['invoice_number'] ?? 'SO-'.now()->format('YmdHis'),
                'branch_id' => $data['branch_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => $request->user()?->id,
                'sale_date' => $data['sale_date'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'status' => $data['status'] ?? 'completed',
                'notes' => $data['notes'] ?? null,
                'subtotal' => 0, 'tax' => 0, 'discount' => 0, 'total' => 0, 'paid' => 0, 'due' => 0,
            ]);

            $this->syncItems($sale, $data['items'], (float) ($data['paid'] ?? 0));

            if ($this->affectsStock($sale->status)) {
                $this->applyStock($sale, $data['items']);
            }
        });

        $flasher->success(__('Sale created successfully'));

        return redirect()->route('admin.sales.index');
    }

    public function edit(Sale $sale): Response
    {
        $sale->load('items');

        return Inertia::render('Sales/Form', [
            'sale' => $sale,
            'options' => $this->options(),
        ]);
    }

    public function update(Request $request, Sale $sale, FlasherInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request);
        $sale->loadMissing('items');

        DB::transaction(function () use ($sale, $data, $request) {
            if ($this->affectsStock($sale->status)) {
                $this->reverseStock($sale);
            }

            $sale->items()->delete();

            $sale->update([
                'branch_id' => $data['branch_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'sale_date' => $data['sale_date'],
                'payment_method' => $data['payment_method'] ?? $sale->payment_method,
                'status' => $data['status'] ?? $sale->status,
                'notes' => $data['notes'] ?? null,
                'user_id' => $request->user()?->id ?? $sale->user_id,
            ]);

            $this->syncItems($sale, $data['items'], (float) ($data['paid'] ?? 0));

            if ($this->affectsStock($sale->status)) {
                $this->applyStock($sale, $data['items']);
            }
        });

        $flasher->success(__('Sale updated successfully'));

        return redirect()->route('admin.sales.index');
    }

    public function destroy(Sale $sale, FlasherInterface $flasher): RedirectResponse
    {
        $sale->loadMissing('items');

        DB::transaction(function () use ($sale) {
            if ($this->affectsStock($sale->status)) {
                $this->reverseStock($sale);
            }

            $sale->delete();
        });

        $flasher->success(__('Sale deleted successfully'));

        return back();
    }

    protected function syncItems(Sale $sale, array $items, float $paid): void
    {
        $subtotal = 0;
        $tax = 0;
        $discount = 0;

        foreach ($items as $itemData) {
            $lineQty = (float) $itemData['quantity'];
            $linePrice = (float) $itemData['sale_price'];
            $lineTaxRate = (float) ($itemData['tax_rate'] ?? 0);
            $lineDiscount = (float) ($itemData['discount'] ?? 0);
            $lineSubtotal = ($lineQty * $linePrice) - $lineDiscount;
            $lineTax = $lineSubtotal * ($lineTaxRate / 100);

            $subtotal += $lineSubtotal;
            $tax += $lineTax;
            $discount += $lineDiscount;

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $lineQty,
                'sale_price' => $linePrice,
                'tax_rate' => $lineTaxRate,
                'discount' => $lineDiscount,
                'subtotal' => $lineSubtotal + $lineTax,
            ]);
        }

        $total = $subtotal + $tax;
        $recordedPaid = (float) $sale->payments()->sum('amount');
        $effectivePaid = $recordedPaid > 0 ? $recordedPaid : $paid;

        $sale->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
            'paid' => min($effectivePaid, $total),
            'due' => max(0, $total - $effectivePaid),
        ]);

        $this->paymentService->syncPayable($sale);
    }

    protected function affectsStock(string $status): bool
    {
        return $status === 'completed';
    }

    protected function applyStock(Sale $sale, array $items): void
    {
        foreach ($items as $itemData) {
            $this->stockService->decrement($itemData['product_id'], $sale->branch_id, (float) $itemData['quantity']);
        }
    }

    protected function reverseStock(Sale $sale): void
    {
        foreach ($sale->items as $existing) {
            $this->stockService->increment(
                $existing->product_id,
                $sale->branch_id,
                (float) $existing->quantity
            );
        }
    }

    protected function options(): array
    {
        return [
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'sku', 'name', 'sale_price', 'tax_rate']),
            'payment_methods' => ['cash', 'card', 'bank_transfer', 'mobile_payment'],
            'statuses' => ['draft', 'completed', 'cancelled', 'refunded'],
        ];
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'invoice_number' => ['nullable', 'string', 'max:64'],
            'branch_id' => ['required', 'exists:branches,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'sale_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'in:cash,card,bank_transfer,mobile_payment'],
            'status' => ['nullable', 'in:draft,completed,cancelled,refunded'],
            'paid' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.sale_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);
    }
}
