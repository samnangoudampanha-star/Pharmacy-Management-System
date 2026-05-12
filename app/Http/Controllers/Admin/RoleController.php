<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Flasher\Prime\FlasherInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Roles/Index', [
            'ajax_url' => route('admin.roles.datatable'),
        ]);
    }

    public function datatable()
    {
        $query = Role::query()->select(['id', 'name', 'display_name', 'description', 'created_at'])->withCount('permissions');

        return DataTables::eloquent($query)
            ->addColumn('actions', fn (Role $role) => [
                'edit_url' => route('admin.roles.edit', $role),
                'delete_url' => route('admin.roles.destroy', $role),
            ])
            ->editColumn('created_at', fn (Role $role) => $role->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Roles/Form', [
            'role' => null,
            'options' => $this->options(),
        ]);
    }

    public function store(Request $request, FlasherInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request);

        $role = Role::create($data);
        $role->permissions()->sync($data['permission_ids'] ?? []);

        $flasher->success(__('Role created successfully'));

        return redirect()->route('admin.roles.index');
    }

    public function edit(Role $role): Response
    {
        $role->load('permissions:id');

        return Inertia::render('Roles/Form', [
            'role' => [
                ...$role->only(['id', 'name', 'display_name', 'description']),
                'permission_ids' => $role->permissions->pluck('id')->all(),
            ],
            'options' => $this->options(),
        ]);
    }

    public function update(Request $request, Role $role, FlasherInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request, $role->id);

        $role->update($data);
        $role->permissions()->sync($data['permission_ids'] ?? []);

        $flasher->success(__('Role updated successfully'));

        return redirect()->route('admin.roles.index');
    }

    public function destroy(Role $role, FlasherInterface $flasher): RedirectResponse
    {
        $role->delete();
        $flasher->success(__('Role deleted successfully'));

        return back();
    }

    protected function options(): array
    {
        $permissions = Permission::query()
            ->orderBy('group')
            ->orderBy('name')
            ->get(['id', 'name', 'display_name', 'group'])
            ->groupBy(fn (Permission $permission) => $permission->group ?: 'general')
            ->map(fn ($group) => $group->map(fn (Permission $permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
                'display_name' => $permission->display_name,
            ])->values())
            ->all();

        return ['permissions' => $permissions];
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('roles', 'name')->ignore($ignoreId)],
            'display_name' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);
    }
}
