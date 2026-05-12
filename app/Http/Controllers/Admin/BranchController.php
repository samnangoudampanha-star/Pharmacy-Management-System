<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Flasher\SweetAlert\Prime\SweetAlertInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class BranchController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Branches/Index', [
            'ajax_url' => route('admin.branches.datatable'),
        ]);
    }

    public function datatable(Request $request)
    {
        $query = Branch::query()->select([
            'id', 'code', 'name', 'phone', 'email', 'city', 'is_main', 'is_active', 'created_at',
        ]);

        return DataTables::eloquent($query)
            ->addColumn('actions', function (Branch $branch) {
                return [
                    'edit_url' => route('admin.branches.edit', $branch),
                    'delete_url' => route('admin.branches.destroy', $branch),
                ];
            })
            ->editColumn('created_at', fn (Branch $b) => $b->created_at?->format('Y-m-d H:i'))
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Branches/Form', [
            'branch' => null,
        ]);
    }

    public function store(Request $request, SweetAlertInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request);
        Branch::create($data);
        $flasher->addSuccess(__('Branch created successfully'));

        return redirect()->route('admin.branches.index');
    }

    public function edit(Branch $branch): Response
    {
        return Inertia::render('Branches/Form', [
            'branch' => $branch,
        ]);
    }

    public function update(Request $request, Branch $branch, SweetAlertInterface $flasher): RedirectResponse
    {
        $branch->update($this->validated($request, $branch->id));
        $flasher->addSuccess(__('Branch updated successfully'));

        return redirect()->route('admin.branches.index');
    }

    public function destroy(Branch $branch, SweetAlertInterface $flasher): RedirectResponse
    {
        $branch->delete();
        $flasher->addSuccess(__('Branch deleted successfully'));

        return back();
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:32', 'unique:branches,code'.($ignoreId ? ','.$ignoreId : '')],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'is_main' => ['boolean'],
            'is_active' => ['boolean'],
        ]);
    }
}
