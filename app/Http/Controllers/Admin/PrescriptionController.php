<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Product;
use Flasher\SweetAlert\Prime\SweetAlertInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class PrescriptionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Prescriptions/Index', [
            'ajax_url' => route('admin.prescriptions.datatable'),
        ]);
    }

    public function datatable()
    {
        $query = Prescription::query()
            ->select(['id', 'reference_number', 'branch_id', 'customer_id', 'prescription_date',
                'doctor_name', 'created_at'])
            ->with(['branch:id,name', 'customer:id,name']);

        return DataTables::eloquent($query)
            ->addColumn('branch_name', fn (Prescription $p) => optional($p->branch)->name)
            ->addColumn('customer_name', fn (Prescription $p) => optional($p->customer)->name)
            ->addColumn('actions', fn (Prescription $p) => [
                'edit_url' => route('admin.prescriptions.edit', $p),
                'delete_url' => route('admin.prescriptions.destroy', $p),
            ])
            ->editColumn('prescription_date', fn (Prescription $p) => $p->prescription_date?->format('Y-m-d'))
            ->editColumn('created_at', fn (Prescription $p) => $p->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Prescriptions/Form', [
            'prescription' => null,
            'options' => $this->options(),
        ]);
    }

    public function store(Request $request, SweetAlertInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $rx = Prescription::create([
                'reference_number' => $data['reference_number'] ?? 'RX-'.now()->format('YmdHis'),
                'branch_id' => $data['branch_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'prescription_date' => $data['prescription_date'],
                'doctor_name' => $data['doctor_name'] ?? null,
                'diagnosis' => $data['diagnosis'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                PrescriptionItem::create([
                    'prescription_id' => $rx->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'dosage' => $item['dosage'] ?? null,
                    'instructions' => $item['instructions'] ?? null,
                ]);
            }
        });

        $flasher->addSuccess(__('Prescription created successfully'));

        return redirect()->route('admin.prescriptions.index');
    }

    public function edit(Prescription $prescription): Response
    {
        $prescription->load('items');

        return Inertia::render('Prescriptions/Form', [
            'prescription' => $prescription,
            'options' => $this->options(),
        ]);
    }

    public function update(Request $request, Prescription $prescription, SweetAlertInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($prescription, $data) {
            $prescription->items()->delete();
            $prescription->update([
                'branch_id' => $data['branch_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'prescription_date' => $data['prescription_date'],
                'doctor_name' => $data['doctor_name'] ?? null,
                'diagnosis' => $data['diagnosis'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'dosage' => $item['dosage'] ?? null,
                    'instructions' => $item['instructions'] ?? null,
                ]);
            }
        });

        $flasher->addSuccess(__('Prescription updated successfully'));

        return redirect()->route('admin.prescriptions.index');
    }

    public function destroy(Prescription $prescription, SweetAlertInterface $flasher): RedirectResponse
    {
        $prescription->delete();
        $flasher->addSuccess(__('Prescription deleted successfully'));

        return back();
    }

    protected function options(): array
    {
        return [
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'sku', 'name']),
        ];
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'reference_number' => ['nullable', 'string', 'max:64'],
            'branch_id' => ['required', 'exists:branches,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'prescription_date' => ['required', 'date'],
            'doctor_name' => ['nullable', 'string', 'max:120'],
            'diagnosis' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.dosage' => ['nullable', 'string', 'max:120'],
            'items.*.instructions' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
