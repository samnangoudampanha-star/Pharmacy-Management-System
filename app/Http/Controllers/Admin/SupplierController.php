<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Flasher\SweetAlert\Prime\SweetAlertInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Suppliers/Index', [
            'ajax_url' => route('admin.suppliers.datatable'),
        ]);
    }

    public function datatable()
    {
        return DataTables::eloquent(Supplier::query()->select([
            'id', 'code', 'name', 'contact_person', 'phone', 'email',
            'city', 'opening_balance', 'is_active', 'created_at',
        ]))
            ->addColumn('actions', fn (Supplier $s) => [
                'edit_url' => route('admin.suppliers.edit', $s),
                'delete_url' => route('admin.suppliers.destroy', $s),
            ])
            ->editColumn('created_at', fn (Supplier $s) => $s->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Suppliers/Form', ['supplier' => null]);
    }

    public function store(Request $request, SweetAlertInterface $flasher): RedirectResponse
    {
        Supplier::create($this->validated($request));
        $flasher->addSuccess(__('Supplier created successfully'));

        return redirect()->route('admin.suppliers.index');
    }

    public function edit(Supplier $supplier): Response
    {
        return Inertia::render('Suppliers/Form', ['supplier' => $supplier]);
    }

    public function update(Request $request, Supplier $supplier, SweetAlertInterface $flasher): RedirectResponse
    {
        $supplier->update($this->validated($request, $supplier->id));
        $flasher->addSuccess(__('Supplier updated successfully'));

        return redirect()->route('admin.suppliers.index');
    }

    public function destroy(Supplier $supplier, SweetAlertInterface $flasher): RedirectResponse
    {
        $supplier->delete();
        $flasher->addSuccess(__('Supplier deleted successfully'));

        return back();
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:32', 'unique:suppliers,code'.($ignoreId ? ','.$ignoreId : '')],
            'name' => ['required', 'string', 'max:120'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'opening_balance' => ['nullable', 'numeric'],
            'is_active' => ['boolean'],
        ]);
    }
}
