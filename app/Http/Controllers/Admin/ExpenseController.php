<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Flasher\SweetAlert\Prime\SweetAlertInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Expenses/Index', [
            'ajax_url' => route('admin.expenses.datatable'),
        ]);
    }

    public function datatable()
    {
        $query = Expense::query()
            ->select(['id', 'reference_number', 'branch_id', 'expense_category_id',
                'expense_date', 'title', 'amount', 'created_at'])
            ->with(['branch:id,name', 'category:id,name']);

        return DataTables::eloquent($query)
            ->addColumn('branch_name', fn (Expense $e) => optional($e->branch)->name)
            ->addColumn('category_name', fn (Expense $e) => optional($e->category)->name)
            ->addColumn('actions', fn (Expense $e) => [
                'edit_url' => route('admin.expenses.edit', $e),
                'delete_url' => route('admin.expenses.destroy', $e),
            ])
            ->editColumn('expense_date', fn (Expense $e) => $e->expense_date?->format('Y-m-d'))
            ->editColumn('created_at', fn (Expense $e) => $e->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Expenses/Form', [
            'expense' => null,
            'options' => $this->options(),
        ]);
    }

    public function store(Request $request, SweetAlertInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request);
        $data['reference_number'] = $data['reference_number'] ?? 'EXP-'.now()->format('YmdHis');
        $data['user_id'] = $request->user()?->id;

        Expense::create($data);
        $flasher->addSuccess(__('Expense recorded successfully'));

        return redirect()->route('admin.expenses.index');
    }

    public function edit(Expense $expense): Response
    {
        return Inertia::render('Expenses/Form', [
            'expense' => $expense,
            'options' => $this->options(),
        ]);
    }

    public function update(Request $request, Expense $expense, SweetAlertInterface $flasher): RedirectResponse
    {
        $expense->update($this->validated($request));
        $flasher->addSuccess(__('Expense updated successfully'));

        return redirect()->route('admin.expenses.index');
    }

    public function destroy(Expense $expense, SweetAlertInterface $flasher): RedirectResponse
    {
        $expense->delete();
        $flasher->addSuccess(__('Expense deleted successfully'));

        return back();
    }

    protected function options(): array
    {
        return [
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'categories' => ExpenseCategory::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'reference_number' => ['nullable', 'string', 'max:64'],
            'branch_id' => ['required', 'exists:branches,id'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'expense_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:160'],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
