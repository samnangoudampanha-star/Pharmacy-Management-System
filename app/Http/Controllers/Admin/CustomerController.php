<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Flasher\SweetAlert\Prime\SweetAlertInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Customers/Index', [
            'ajax_url' => route('admin.customers.datatable'),
        ]);
    }

    public function datatable()
    {
        return DataTables::eloquent(Customer::query()->select([
            'id', 'code', 'name', 'phone', 'email', 'gender', 'is_active', 'created_at',
        ]))
            ->addColumn('actions', fn (Customer $c) => [
                'edit_url' => route('admin.customers.edit', $c),
                'delete_url' => route('admin.customers.destroy', $c),
            ])
            ->editColumn('created_at', fn (Customer $c) => $c->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Customers/Form', ['customer' => null]);
    }

    public function store(Request $request, SweetAlertInterface $flasher): RedirectResponse
    {
        Customer::create($this->validated($request));
        $flasher->addSuccess(__('Customer created successfully'));

        return redirect()->route('admin.customers.index');
    }

    public function edit(Customer $customer): Response
    {
        return Inertia::render('Customers/Form', ['customer' => $customer]);
    }

    public function update(Request $request, Customer $customer, SweetAlertInterface $flasher): RedirectResponse
    {
        $customer->update($this->validated($request, $customer->id));
        $flasher->addSuccess(__('Customer updated successfully'));

        return redirect()->route('admin.customers.index');
    }

    public function destroy(Customer $customer, SweetAlertInterface $flasher): RedirectResponse
    {
        $customer->delete();
        $flasher->addSuccess(__('Customer deleted successfully'));

        return back();
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:32', 'unique:customers,code'.($ignoreId ? ','.$ignoreId : '')],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'opening_balance' => ['nullable', 'numeric'],
            'is_active' => ['boolean'],
        ]);
    }
}
