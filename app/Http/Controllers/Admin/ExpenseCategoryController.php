<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Flasher\SweetAlert\Prime\SweetAlertInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class ExpenseCategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('ExpenseCategories/Index', [
            'ajax_url' => route('admin.expense-categories.datatable'),
        ]);
    }

    public function datatable()
    {
        return DataTables::eloquent(ExpenseCategory::query()->select([
            'id', 'name', 'description', 'is_active', 'created_at',
        ]))
            ->addColumn('actions', fn (ExpenseCategory $c) => [
                'edit_url' => route('admin.expense-categories.edit', $c),
                'delete_url' => route('admin.expense-categories.destroy', $c),
            ])
            ->editColumn('created_at', fn (ExpenseCategory $c) => $c->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('ExpenseCategories/Form', ['category' => null]);
    }

    public function store(Request $request, SweetAlertInterface $flasher): RedirectResponse
    {
        ExpenseCategory::create($this->validated($request));
        $flasher->addSuccess(__('Expense category created successfully'));

        return redirect()->route('admin.expense-categories.index');
    }

    public function edit(ExpenseCategory $expenseCategory): Response
    {
        return Inertia::render('ExpenseCategories/Form', ['category' => $expenseCategory]);
    }

    public function update(Request $request, ExpenseCategory $expenseCategory, SweetAlertInterface $flasher): RedirectResponse
    {
        $expenseCategory->update($this->validated($request, $expenseCategory->id));
        $flasher->addSuccess(__('Expense category updated successfully'));

        return redirect()->route('admin.expense-categories.index');
    }

    public function destroy(ExpenseCategory $expenseCategory, SweetAlertInterface $flasher): RedirectResponse
    {
        $expenseCategory->delete();
        $flasher->addSuccess(__('Expense category deleted successfully'));

        return back();
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:expense_categories,name'.($ignoreId ? ','.$ignoreId : '')],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
    }
}
