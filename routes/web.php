<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ManufacturerController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PrescriptionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\StockTransferController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.dashboard'));

// Locale switching (no full page reload — only persists locale on the server).
Route::post('/locale', [LocaleController::class, 'switch'])->name('locale.switch');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'active'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    // Data masters
    Route::middleware('permission:branches.manage')->group(function () {
        Route::resource('branches', BranchController::class)->except('show');
        Route::get('branches/datatable', [BranchController::class, 'datatable'])->name('branches.datatable');
    });

    Route::middleware('permission:categories.manage')->group(function () {
        Route::resource('categories', CategoryController::class)->except('show');
        Route::get('categories/datatable', [CategoryController::class, 'datatable'])->name('categories.datatable');
    });

    Route::middleware('permission:units.manage')->group(function () {
        Route::resource('units', UnitController::class)->except('show');
        Route::get('units/datatable', [UnitController::class, 'datatable'])->name('units.datatable');
    });

    Route::middleware('permission:manufacturers.manage')->group(function () {
        Route::resource('manufacturers', ManufacturerController::class)->except('show');
        Route::get('manufacturers/datatable', [ManufacturerController::class, 'datatable'])->name('manufacturers.datatable');
    });

    Route::middleware('permission:suppliers.manage')->group(function () {
        Route::resource('suppliers', SupplierController::class)->except('show');
        Route::get('suppliers/datatable', [SupplierController::class, 'datatable'])->name('suppliers.datatable');
    });

    Route::middleware('permission:customers.manage')->group(function () {
        Route::resource('customers', CustomerController::class)->except('show');
        Route::get('customers/datatable', [CustomerController::class, 'datatable'])->name('customers.datatable');
    });

    Route::middleware('permission:users.manage')->group(function () {
        Route::resource('users', UserController::class)->except('show');
        Route::get('users/datatable', [UserController::class, 'datatable'])->name('users.datatable');
    });

    Route::middleware('permission:roles.manage')->group(function () {
        Route::resource('roles', RoleController::class)->except('show');
        Route::get('roles/datatable', [RoleController::class, 'datatable'])->name('roles.datatable');
    });

    Route::middleware('permission:permissions.manage')->group(function () {
        Route::resource('permissions', PermissionController::class)->except('show');
        Route::get('permissions/datatable', [PermissionController::class, 'datatable'])->name('permissions.datatable');
    });

    // Inventory
    Route::middleware('permission:products.manage')->group(function () {
        Route::resource('products', ProductController::class)->except('show');
        Route::get('products/datatable', [ProductController::class, 'datatable'])->name('products.datatable');
    });

    Route::middleware('permission:stocks.view')->group(function () {
        Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
        Route::get('stocks/datatable', [StockController::class, 'datatable'])->name('stocks.datatable');
    });

    Route::middleware('permission:stock_transfers.manage')->group(function () {
        Route::resource('stock-transfers', StockTransferController::class)->except('show');
        Route::get('stock-transfers/datatable', [StockTransferController::class, 'datatable'])->name('stock-transfers.datatable');
    });

    Route::middleware('permission:stock_adjustments.manage')->group(function () {
        Route::resource('stock-adjustments', StockAdjustmentController::class)->except('show');
        Route::get('stock-adjustments/datatable', [StockAdjustmentController::class, 'datatable'])->name('stock-adjustments.datatable');
    });

    // Operations
    Route::middleware('permission:purchases.manage')->group(function () {
        Route::resource('purchases', PurchaseController::class)->except('show');
        Route::get('purchases/datatable', [PurchaseController::class, 'datatable'])->name('purchases.datatable');
    });

    Route::middleware('permission:sales.manage')->group(function () {
        Route::resource('sales', SaleController::class)->except('show');
        Route::get('sales/datatable', [SaleController::class, 'datatable'])->name('sales.datatable');
    });

    Route::middleware('permission:prescriptions.manage')->group(function () {
        Route::resource('prescriptions', PrescriptionController::class)->except('show');
        Route::get('prescriptions/datatable', [PrescriptionController::class, 'datatable'])->name('prescriptions.datatable');
    });

    // Finance
    Route::middleware('permission:expense_categories.manage')->group(function () {
        Route::resource('expense-categories', ExpenseCategoryController::class)->except('show');
        Route::get('expense-categories/datatable', [ExpenseCategoryController::class, 'datatable'])
            ->name('expense-categories.datatable');
    });

    Route::middleware('permission:expenses.manage')->group(function () {
        Route::resource('expenses', ExpenseController::class)->except('show');
        Route::get('expenses/datatable', [ExpenseController::class, 'datatable'])->name('expenses.datatable');
    });

    Route::middleware('permission:payments.manage')->group(function () {
        Route::resource('payments', PaymentController::class)->except('show');
        Route::get('payments/datatable', [PaymentController::class, 'datatable'])->name('payments.datatable');
    });
});
