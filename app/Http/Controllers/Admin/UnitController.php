<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Flasher\SweetAlert\Prime\SweetAlertInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class UnitController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Units/Index', [
            'ajax_url' => route('admin.units.datatable'),
        ]);
    }

    public function datatable()
    {
        return DataTables::eloquent(Unit::query()->select(['id', 'name', 'symbol', 'description', 'created_at']))
            ->addColumn('actions', fn (Unit $u) => [
                'edit_url' => route('admin.units.edit', $u),
                'delete_url' => route('admin.units.destroy', $u),
            ])
            ->editColumn('created_at', fn (Unit $u) => $u->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Units/Form', ['unit' => null]);
    }

    public function store(Request $request, SweetAlertInterface $flasher): RedirectResponse
    {
        Unit::create($this->validated($request));
        $flasher->addSuccess(__('Unit created successfully'));

        return redirect()->route('admin.units.index');
    }

    public function edit(Unit $unit): Response
    {
        return Inertia::render('Units/Form', ['unit' => $unit]);
    }

    public function update(Request $request, Unit $unit, SweetAlertInterface $flasher): RedirectResponse
    {
        $unit->update($this->validated($request, $unit->id));
        $flasher->addSuccess(__('Unit updated successfully'));

        return redirect()->route('admin.units.index');
    }

    public function destroy(Unit $unit, SweetAlertInterface $flasher): RedirectResponse
    {
        $unit->delete();
        $flasher->addSuccess(__('Unit deleted successfully'));

        return back();
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'symbol' => ['required', 'string', 'max:32', 'unique:units,symbol'.($ignoreId ? ','.$ignoreId : '')],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
