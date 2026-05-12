<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Flasher\SweetAlert\Prime\SweetAlertInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Users/Index', [
            'ajax_url' => route('admin.users.datatable'),
        ]);
    }

    public function datatable()
    {
        $query = User::query()
            ->select(['id', 'name', 'email', 'phone', 'branch_id', 'role_id', 'is_active', 'created_at'])
            ->with(['branch:id,name', 'role:id,name,display_name']);

        return DataTables::eloquent($query)
            ->addColumn('branch_name', fn (User $u) => optional($u->branch)->name)
            ->addColumn('role_name', fn (User $u) => optional($u->role)->display_name ?: optional($u->role)->name)
            ->addColumn('actions', fn (User $u) => [
                'edit_url' => route('admin.users.edit', $u),
                'delete_url' => route('admin.users.destroy', $u),
            ])
            ->editColumn('created_at', fn (User $u) => $u->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Users/Form', [
            'user' => null,
            'options' => $this->options(),
        ]);
    }

    public function store(Request $request, SweetAlertInterface $flasher): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'is_active' => ['boolean'],
        ]);

        User::create($data);
        $flasher->addSuccess(__('User created successfully'));

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Users/Form', [
            'user' => $user->only([
                'id', 'name', 'email', 'phone', 'address', 'branch_id', 'role_id', 'is_active',
            ]),
            'options' => $this->options(),
        ]);
    }

    public function update(Request $request, User $user, SweetAlertInterface $flasher): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'is_active' => ['boolean'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);
        $flasher->addSuccess(__('User updated successfully'));

        return redirect()->route('admin.users.index');
    }

    public function destroy(User $user, SweetAlertInterface $flasher): RedirectResponse
    {
        $user->delete();
        $flasher->addSuccess(__('User deleted successfully'));

        return back();
    }

    protected function options(): array
    {
        return [
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'roles' => Role::orderBy('name')->get(['id', 'name', 'display_name']),
        ];
    }
}
