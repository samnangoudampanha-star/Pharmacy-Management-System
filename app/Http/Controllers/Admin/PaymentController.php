<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Services\PaymentService;
use Flasher\Prime\FlasherInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    protected const PAYABLE_TYPES = [
        'sale' => Sale::class,
        'purchase' => Purchase::class,
        'expense' => Expense::class,
        'supplier' => Supplier::class,
        'customer' => Customer::class,
    ];

    public function __construct(protected PaymentService $paymentService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Payments/Index', [
            'ajax_url' => route('admin.payments.datatable'),
        ]);
    }

    public function datatable()
    {
        $query = Payment::query()
            ->select(['id', 'reference_number', 'branch_id', 'user_id', 'payable_type', 'payable_id', 'payment_date', 'amount', 'method', 'created_at'])
            ->with(['branch:id,name', 'user:id,name', 'payable']);

        return DataTables::eloquent($query)
            ->addColumn('branch_name', fn (Payment $payment) => optional($payment->branch)->name)
            ->addColumn('payable_type_label', fn (Payment $payment) => str(class_basename($payment->payable_type))->headline()->toString())
            ->addColumn('payable_label', fn (Payment $payment) => $this->formatPayableLabel($payment->payable))
            ->addColumn('user_name', fn (Payment $payment) => optional($payment->user)->name)
            ->addColumn('actions', fn (Payment $payment) => [
                'edit_url' => route('admin.payments.edit', $payment),
                'delete_url' => route('admin.payments.destroy', $payment),
            ])
            ->editColumn('payment_date', fn (Payment $payment) => $payment->payment_date?->format('Y-m-d'))
            ->editColumn('created_at', fn (Payment $payment) => $payment->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Payments/Form', [
            'payment' => null,
            'options' => $this->options(),
        ]);
    }

    public function store(Request $request, FlasherInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $request) {
            $payment = Payment::create([
                'reference_number' => $data['reference_number'] ?: 'PAY-'.now()->format('YmdHis'),
                'branch_id' => $data['branch_id'],
                'user_id' => $request->user()?->id,
                'payable_type' => self::PAYABLE_TYPES[$data['payable_type']],
                'payable_id' => $data['payable_id'],
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'method' => $data['method'],
                'notes' => $data['notes'] ?? null,
            ]);

            if ($payment->payable) {
                $this->paymentService->syncPayable($payment->payable);
            }
        });

        $flasher->success(__('Payment recorded successfully'));

        return redirect()->route('admin.payments.index');
    }

    public function edit(Payment $payment): Response
    {
        return Inertia::render('Payments/Form', [
            'payment' => [
                ...$payment->only(['id', 'reference_number', 'branch_id', 'payable_id', 'payment_date', 'amount', 'method', 'notes']),
                'payable_type' => $this->payableTypeKey($payment->payable_type),
            ],
            'options' => $this->options(),
        ]);
    }

    public function update(Request $request, Payment $payment, FlasherInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request);
        $previousPayable = $payment->payable;

        DB::transaction(function () use ($data, $payment, $request, $previousPayable) {
            $payment->update([
                'reference_number' => $data['reference_number'] ?: $payment->reference_number,
                'branch_id' => $data['branch_id'],
                'user_id' => $request->user()?->id ?? $payment->user_id,
                'payable_type' => self::PAYABLE_TYPES[$data['payable_type']],
                'payable_id' => $data['payable_id'],
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'method' => $data['method'],
                'notes' => $data['notes'] ?? null,
            ]);

            if ($previousPayable) {
                $this->paymentService->syncPayable($previousPayable);
            }

            if ($payment->payable) {
                $this->paymentService->syncPayable($payment->payable);
            }
        });

        $flasher->success(__('Payment updated successfully'));

        return redirect()->route('admin.payments.index');
    }

    public function destroy(Payment $payment, FlasherInterface $flasher): RedirectResponse
    {
        $payable = $payment->payable;

        DB::transaction(function () use ($payment, $payable) {
            $payment->delete();

            if ($payable) {
                $this->paymentService->syncPayable($payable);
            }
        });

        $flasher->success(__('Payment deleted successfully'));

        return back();
    }

    protected function options(): array
    {
        return [
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'methods' => ['cash', 'card', 'bank_transfer', 'mobile_payment'],
            'payable_types' => array_map(
                fn (string $key) => ['value' => $key, 'label' => str($key)->headline()->toString()],
                array_keys(self::PAYABLE_TYPES)
            ),
            'payables' => [
                'sale' => Sale::query()->orderByDesc('sale_date')->get(['id', 'invoice_number', 'total', 'due'])
                    ->map(fn (Sale $sale) => ['id' => $sale->id, 'label' => $sale->invoice_number.' - '.$this->formatAmount($sale->due).' '.__('due')])
                    ->values(),
                'purchase' => Purchase::query()->orderByDesc('purchase_date')->get(['id', 'reference_number', 'total', 'due'])
                    ->map(fn (Purchase $purchase) => ['id' => $purchase->id, 'label' => $purchase->reference_number.' - '.$this->formatAmount($purchase->due).' '.__('due')])
                    ->values(),
                'expense' => Expense::query()->orderByDesc('expense_date')->get(['id', 'reference_number', 'title', 'amount'])
                    ->map(fn (Expense $expense) => ['id' => $expense->id, 'label' => $expense->reference_number.' - '.$expense->title])
                    ->values(),
                'supplier' => Supplier::query()->orderBy('name')->get(['id', 'code', 'name'])
                    ->map(fn (Supplier $supplier) => ['id' => $supplier->id, 'label' => $supplier->code.' - '.$supplier->name])
                    ->values(),
                'customer' => Customer::query()->orderBy('name')->get(['id', 'code', 'name'])
                    ->map(fn (Customer $customer) => ['id' => $customer->id, 'label' => $customer->code.' - '.$customer->name])
                    ->values(),
            ],
        ];
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'reference_number' => ['nullable', 'string', 'max:64'],
            'branch_id' => ['required', 'exists:branches,id'],
            'payable_type' => ['required', Rule::in(array_keys(self::PAYABLE_TYPES))],
            'payable_id' => ['required', 'integer'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,card,bank_transfer,mobile_payment'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $modelClass = self::PAYABLE_TYPES[$data['payable_type']];

        if (! $modelClass::query()->whereKey($data['payable_id'])->exists()) {
            throw ValidationException::withMessages([
                'payable_id' => __('The selected payable record is invalid.'),
            ]);
        }

        return $data;
    }

    protected function payableTypeKey(?string $className): ?string
    {
        return array_search($className, self::PAYABLE_TYPES, true) ?: null;
    }

    protected function formatPayableLabel(?Model $payable): string
    {
        return match (true) {
            $payable instanceof Sale => $payable->invoice_number,
            $payable instanceof Purchase => $payable->reference_number,
            $payable instanceof Expense => $payable->reference_number.' - '.$payable->title,
            $payable instanceof Supplier => $payable->code.' - '.$payable->name,
            $payable instanceof Customer => $payable->code.' - '.$payable->name,
            default => '-',
        };
    }

    protected function formatAmount(mixed $value): string
    {
        return number_format((float) $value, 2);
    }
}
