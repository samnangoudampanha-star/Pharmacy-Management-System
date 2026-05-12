<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Flasher\SweetAlert\Prime\SweetAlertInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Categories/Index', [
            'ajax_url' => route('admin.categories.datatable'),
        ]);
    }

    public function datatable()
    {
        $query = Category::query()->select(['id', 'name', 'slug', 'description', 'is_active', 'created_at']);

        return DataTables::eloquent($query)
            ->addColumn('actions', fn (Category $c) => [
                'edit_url' => route('admin.categories.edit', $c),
                'delete_url' => route('admin.categories.destroy', $c),
            ])
            ->editColumn('created_at', fn (Category $c) => $c->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Categories/Form', ['category' => null]);
    }

    public function store(Request $request, SweetAlertInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        Category::create($data);
        $flasher->addSuccess(__('Category created successfully'));

        return redirect()->route('admin.categories.index');
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('Categories/Form', ['category' => $category]);
    }

    public function update(Request $request, Category $category, SweetAlertInterface $flasher): RedirectResponse
    {
        $data = $this->validated($request, $category->id);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $category->update($data);
        $flasher->addSuccess(__('Category updated successfully'));

        return redirect()->route('admin.categories.index');
    }

    public function destroy(Category $category, SweetAlertInterface $flasher): RedirectResponse
    {
        $category->delete();
        $flasher->addSuccess(__('Category deleted successfully'));

        return back();
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:categories,slug'.($ignoreId ? ','.$ignoreId : '')],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
    }
}
