<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Flasher\Prime\FlasherInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Permissions/Index', [
            'ajax_url' => route('admin.permissions.datatable'),
        ]);
    }

    public function datatable()
    {
        $query = Permission::query()->select(['id', 'name', 'display_name', 'group', 'created_at'])->withCount('roles');

        return DataTables::eloquent($query)
            ->addColumn('actions', fn (Permission $permission) => [
                'edit_url' => route('admin.permissions.edit', $permission),
                'delete_url' => route('admin.permissions.destroy', $permission),
            ])
            ->editColumn('created_at', fn (Permission $permission) => $permission->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Permissions/Form', [
            'permission' => null,
        ]);
    }

    public function store(Request $request, FlasherInterface $flasher): RedirectResponse
    {
        Permission::create($this->validated($request));
        $flasher->success(__('Permission created successfully'));

        return redirect()->route('admin.permissions.index');
    }

    public function edit(Permission $permission): Response
    {
        return Inertia::render('Permissions/Form', [
            'permission' => $permission->only(['id', 'name', 'display_name', 'group']),
        ]);
    }

    public function update(Request $request, Permission $permission, FlasherInterface $flasher): RedirectResponse
    {
        $permission->update($this->validated($request, $permission->id));
        $flasher->success(__('Permission updated successfully'));

        return redirect()->route('admin.permissions.index');
    }

    public function destroy(Permission $permission, FlasherInterface $flasher): RedirectResponse
    {
        $permission->delete();
        $flasher->success(__('Permission deleted successfully'));

        return back();
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('permissions', 'name')->ignore($ignoreId)],
            'display_name' => ['nullable', 'string', 'max:120'],
            'group' => ['nullable', 'string', 'max:120'],
        ]);
    }
}
