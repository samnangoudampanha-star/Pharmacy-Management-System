<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmacyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_for_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_user_without_permission_gets_forbidden_on_payments_index(): void
    {
        $user = $this->createUserWithRole('cashier');

        $this->actingAs($user)
            ->get(route('admin.payments.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_permission_and_role_with_permission_assignment(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin)
            ->post(route('admin.permissions.store'), [
                'name' => 'reports.view',
                'display_name' => 'Reports View',
                'group' => 'reports',
            ])
            ->assertRedirect(route('admin.permissions.index'));

        $permission = Permission::firstWhere('name', 'reports.view');

        $this->assertNotNull($permission);

        $this->actingAs($admin)
            ->post(route('admin.roles.store'), [
                'name' => 'manager',
                'display_name' => 'Manager',
                'description' => 'Store manager',
                'permission_ids' => [$permission->id],
            ])
            ->assertRedirect(route('admin.roles.index'));

        $role = Role::where('name', 'manager')->firstOrFail();

        $this->assertTrue($role->permissions->contains('id', $permission->id));
    }

    public function test_payment_creation_updates_sale_paid_and_due_amounts(): void
    {
        $admin = $this->createAdminUser();
        $branch = $this->createBranch();
        $sale = Sale::create([
            'invoice_number' => 'SO-1001',
            'branch_id' => $branch->id,
            'customer_id' => null,
            'user_id' => $admin->id,
            'sale_date' => now()->toDateString(),
            'subtotal' => 100,
            'discount' => 0,
            'tax' => 0,
            'total' => 100,
            'paid' => 0,
            'due' => 100,
            'payment_method' => 'cash',
            'status' => 'completed',
            'notes' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.payments.store'), [
                'reference_number' => 'PAY-1001',
                'branch_id' => $branch->id,
                'payable_type' => 'sale',
                'payable_id' => $sale->id,
                'payment_date' => now()->toDateString(),
                'amount' => 40,
                'method' => 'cash',
                'notes' => 'Partial payment',
            ])
            ->assertRedirect(route('admin.payments.index'));

        $sale->refresh();

        $this->assertSame(40.0, (float) $sale->paid);
        $this->assertSame(60.0, (float) $sale->due);
    }

    public function test_purchase_status_controls_stock_movements(): void
    {
        $admin = $this->createAdminUser();
        $branch = $this->createBranch();
        $supplier = Supplier::create([
            'code' => 'SUP-001',
            'name' => 'Main Supplier',
            'opening_balance' => 0,
            'is_active' => true,
        ]);
        $product = $this->createProduct();

        $this->actingAs($admin)
            ->post(route('admin.purchases.store'), [
                'reference_number' => 'PO-1001',
                'branch_id' => $branch->id,
                'supplier_id' => $supplier->id,
                'purchase_date' => now()->toDateString(),
                'status' => 'draft',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'cost_price' => 10,
                    'tax_rate' => 0,
                    'discount' => 0,
                ]],
            ])
            ->assertRedirect(route('admin.purchases.index'));

        $purchase = \App\Models\Purchase::firstOrFail();

        $this->assertNull(ProductStock::where('product_id', $product->id)->where('branch_id', $branch->id)->first());

        $this->actingAs($admin)
            ->put(route('admin.purchases.update', $purchase), [
                'reference_number' => 'PO-1001',
                'branch_id' => $branch->id,
                'supplier_id' => $supplier->id,
                'purchase_date' => now()->toDateString(),
                'status' => 'received',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'cost_price' => 10,
                    'tax_rate' => 0,
                    'discount' => 0,
                ]],
            ])
            ->assertRedirect(route('admin.purchases.index'));

        $stock = ProductStock::where('product_id', $product->id)->where('branch_id', $branch->id)->firstOrFail();
        $this->assertSame(5.0, (float) $stock->quantity);

        $this->actingAs($admin)
            ->put(route('admin.purchases.update', $purchase), [
                'reference_number' => 'PO-1001',
                'branch_id' => $branch->id,
                'supplier_id' => $supplier->id,
                'purchase_date' => now()->toDateString(),
                'status' => 'cancelled',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'cost_price' => 10,
                    'tax_rate' => 0,
                    'discount' => 0,
                ]],
            ])
            ->assertRedirect(route('admin.purchases.index'));

        $stock->refresh();
        $this->assertSame(0.0, (float) $stock->quantity);
    }

    public function test_sale_status_and_transfer_workflows_respect_stock_levels(): void
    {
        $admin = $this->createAdminUser();
        $fromBranch = $this->createBranch(['code' => 'MAIN', 'name' => 'Main']);
        $toBranch = $this->createBranch(['code' => 'BR-02', 'name' => 'Branch 02', 'is_main' => false]);
        $product = $this->createProduct();

        ProductStock::create([
            'product_id' => $product->id,
            'branch_id' => $fromBranch->id,
            'quantity' => 10,
            'avg_cost' => 5,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), [
                'invoice_number' => 'SO-2001',
                'branch_id' => $fromBranch->id,
                'sale_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'paid' => 0,
                'status' => 'draft',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'sale_price' => 8,
                    'tax_rate' => 0,
                    'discount' => 0,
                ]],
            ])
            ->assertRedirect(route('admin.sales.index'));

        $sale = Sale::firstOrFail();
        $stock = ProductStock::where('product_id', $product->id)->where('branch_id', $fromBranch->id)->firstOrFail();
        $this->assertSame(10.0, (float) $stock->quantity);

        $this->actingAs($admin)
            ->put(route('admin.sales.update', $sale), [
                'invoice_number' => 'SO-2001',
                'branch_id' => $fromBranch->id,
                'sale_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'paid' => 0,
                'status' => 'completed',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'sale_price' => 8,
                    'tax_rate' => 0,
                    'discount' => 0,
                ]],
            ])
            ->assertRedirect(route('admin.sales.index'));

        $stock->refresh();
        $this->assertSame(7.0, (float) $stock->quantity);

        $this->actingAs($admin)
            ->put(route('admin.sales.update', $sale), [
                'invoice_number' => 'SO-2001',
                'branch_id' => $fromBranch->id,
                'sale_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'paid' => 0,
                'status' => 'refunded',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'sale_price' => 8,
                    'tax_rate' => 0,
                    'discount' => 0,
                ]],
            ])
            ->assertRedirect(route('admin.sales.index'));

        $stock->refresh();
        $this->assertSame(10.0, (float) $stock->quantity);

        $this->actingAs($admin)
            ->post(route('admin.stock-transfers.store'), [
                'reference_number' => 'TR-1001',
                'from_branch_id' => $fromBranch->id,
                'to_branch_id' => $toBranch->id,
                'transfer_date' => now()->toDateString(),
                'status' => 'pending',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 2,
                ]],
            ])
            ->assertRedirect(route('admin.stock-transfers.index'));

        $fromStock = ProductStock::where('product_id', $product->id)->where('branch_id', $fromBranch->id)->firstOrFail();
        $this->assertSame(10.0, (float) $fromStock->quantity);

        $transfer = \App\Models\StockTransfer::firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.stock-transfers.update', $transfer), [
                'reference_number' => 'TR-1001',
                'from_branch_id' => $fromBranch->id,
                'to_branch_id' => $toBranch->id,
                'transfer_date' => now()->toDateString(),
                'status' => 'completed',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 2,
                ]],
            ])
            ->assertRedirect(route('admin.stock-transfers.index'));

        $fromStock->refresh();
        $toStock = ProductStock::where('product_id', $product->id)->where('branch_id', $toBranch->id)->firstOrFail();
        $this->assertSame(8.0, (float) $fromStock->quantity);
        $this->assertSame(2.0, (float) $toStock->quantity);

        $this->actingAs($admin)
            ->post(route('admin.stock-transfers.store'), [
                'reference_number' => 'TR-1002',
                'from_branch_id' => $fromBranch->id,
                'to_branch_id' => $toBranch->id,
                'transfer_date' => now()->toDateString(),
                'status' => 'completed',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 20,
                ]],
            ])
            ->assertSessionHasErrors('items');

        $fromStock->refresh();
        $toStock->refresh();
        $this->assertSame(8.0, (float) $fromStock->quantity);
        $this->assertSame(2.0, (float) $toStock->quantity);
    }

    protected function createAdminUser(): User
    {
        return $this->createUserWithRole('admin');
    }

    protected function createUserWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['display_name' => ucfirst($roleName)]);

        return User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    protected function createBranch(array $attributes = []): Branch
    {
        static $branchIndex = 1;

        $branch = Branch::create(array_merge([
            'code' => 'BR-'.$branchIndex,
            'name' => 'Branch '.$branchIndex,
            'is_main' => $branchIndex === 1,
            'is_active' => true,
        ], $attributes));

        $branchIndex++;

        return $branch;
    }

    protected function createProduct(array $attributes = []): Product
    {
        static $productIndex = 1;

        $productName = 'Product '.$productIndex;

        return Product::create(array_merge([
            'sku' => 'SKU-'.$productIndex++,
            'name' => $productName,
            'cost_price' => 5,
            'sale_price' => 8,
            'tax_rate' => 0,
            'reorder_level' => 0,
            'is_active' => true,
        ], $attributes));
    }
}
