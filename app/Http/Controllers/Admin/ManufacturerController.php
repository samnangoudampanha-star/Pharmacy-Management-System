<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manufacturer;
use Flasher\SweetAlert\Prime\SweetAlertInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class ManufacturerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Manufacturers/Index', [
            'ajax_url' => route('admin.manufacturers.datatable'),
        ]);
    }

    public function datatable()
    {
        return DataTables::eloquent(
            Manufacturer::query()->select(['id', 'name', 'country', 'phone', 'email', 'is_active', 'created_at'])
        )
            ->addColumn('actions', fn (Manufacturer $m) => [
                'edit_url' => route('admin.manufacturers.edit', $m),
                'delete_url' => route('admin.manufacturers.destroy', $m),
            ])
            ->editColumn('created_at', fn (Manufacturer $m) => $m->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Manufacturers/Form', ['manufacturer' => null]);
    }

    public function store(Request $request, SweetAlertInterface $flasher): RedirectResponse
    {
        Manufacturer::create($this->validated($request));
        $flasher->addSuccess(__('Manufacturer created successfully'));

        return redirect()->route('admin.manufacturers.index');
    }

    public function edit(Manufacturer $manufacturer): Response
    {
        return Inertia::render('Manufacturers/Form', ['manufacturer' => $manufacturer]);
    }

    public function update(Request $request, Manufacturer $manufacturer, SweetAlertInterface $flasher): RedirectResponse
    {
        $manufacturer->update($this->validated($request));
        $flasher->addSuccess(__('Manufacturer updated successfully'));

        return redirect()->route('admin.manufacturers.index');
    }

    public function destroy(Manufacturer $manufacturer, SweetAlertInterface $flasher): RedirectResponse
    {
        $manufacturer->delete();
        $flasher->addSuccess(__('Manufacturer deleted successfully'));

        return back();
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
    }
}
