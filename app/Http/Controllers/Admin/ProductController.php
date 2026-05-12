<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\Unit;
use Flasher\SweetAlert\Prime\SweetAlertInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Products/Index', [
            'ajax_url' => route('admin.products.datatable'),
        ]);
    }

    public function datatable()
    {
        $query = Product::query()
            ->select([
                'products.id', 'products.sku', 'products.barcode', 'products.name',
                'products.generic_name', 'products.category_id', 'products.unit_id',
                'products.manufacturer_id', 'products.cost_price', 'products.sale_price',
                'products.reorder_level', 'products.is_active', 'products.expiry_date',
                'products.created_at',
            ])
            ->with([
                'category:id,name',
                'unit:id,name,symbol',
                'manufacturer:id,name',
            ])
            ->withSum('stocks as stock_on_hand', 'quantity');

        return DataTables::eloquent($query)
            ->addColumn('category_name', fn (Product $p) => optional($p->category)->name)
            ->addColumn('unit_name', fn (Product $p) => optional($p->unit)->symbol ?: optional($p->unit)->name)
            ->addColumn('manufacturer_name', fn (Product $p) => optional($p->manufacturer)->name)
            ->addColumn('actions', fn (Product $p) => [
                'edit_url' => route('admin.products.edit', $p),
                'delete_url' => route('admin.products.destroy', $p),
            ])
            ->editColumn('expiry_date', fn (Product $p) => $p->expiry_date?->format('Y-m-d'))
            ->editColumn('created_at', fn (Product $p) => $p->created_at?->format('Y-m-d H:i'))
            ->toJson();
    }

    public function create(): Response
    {
        return Inertia::render('Products/Form', [
            'product' => null,
            'options' => $this->selectOptions(),
        ]);
    }

    public function store(Request $request, SweetAlertInterface $flasher): RedirectResponse
    {
        Product::create($this->validated($request));
        $flasher->addSuccess(__('Product created successfully'));

        return redirect()->route('admin.products.index');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('Products/Form', [
            'product' => $product,
            'options' => $this->selectOptions(),
        ]);
    }

    public function update(Request $request, Product $product, SweetAlertInterface $flasher): RedirectResponse
    {
        $product->update($this->validated($request, $product->id));
        $flasher->addSuccess(__('Product updated successfully'));

        return redirect()->route('admin.products.index');
    }

    public function destroy(Product $product, SweetAlertInterface $flasher): RedirectResponse
    {
        $product->delete();
        $flasher->addSuccess(__('Product deleted successfully'));

        return back();
    }

    protected function selectOptions(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'units' => Unit::orderBy('name')->get(['id', 'name', 'symbol']),
            'manufacturers' => Manufacturer::orderBy('name')->get(['id', 'name']),
        ];
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'sku' => ['required', 'string', 'max:64', 'unique:products,sku'.($ignoreId ? ','.$ignoreId : '')],
            'barcode' => ['nullable', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:160'],
            'generic_name' => ['nullable', 'string', 'max:160'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'manufacturer_id' => ['nullable', 'exists:manufacturers,id'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'manufacture_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'batch_number' => ['nullable', 'string', 'max:64'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);
    }
}
