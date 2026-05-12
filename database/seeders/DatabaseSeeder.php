<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\Manufacturer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissionGroups = [
            'dashboard' => ['dashboard.view'],
            'masters' => ['branches.manage', 'categories.manage', 'units.manage', 'manufacturers.manage'],
            'people' => ['users.manage', 'roles.manage', 'permissions.manage', 'suppliers.manage', 'customers.manage'],
            'inventory' => ['products.manage', 'stocks.view', 'stock_transfers.manage', 'stock_adjustments.manage'],
            'operations' => ['purchases.manage', 'sales.manage', 'prescriptions.manage'],
            'finance' => ['expense_categories.manage', 'expenses.manage', 'payments.manage'],
        ];

        $permissions = collect();

        foreach ($permissionGroups as $group => $names) {
            foreach ($names as $name) {
                $permissions->push(
                    Permission::firstOrCreate(
                        ['name' => $name],
                        [
                            'display_name' => str($name)->replace('.', ' ')->title()->toString(),
                            'group' => $group,
                        ]
                    )
                );
            }
        }

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator', 'description' => 'Full system access']
        );
        $cashierRole = Role::firstOrCreate(
            ['name' => 'cashier'],
            ['display_name' => 'Cashier', 'description' => 'Sales operations']
        );
        $pharmacistRole = Role::firstOrCreate(
            ['name' => 'pharmacist'],
            ['display_name' => 'Pharmacist', 'description' => 'Prescription handling']
        );

        $permissionIds = $permissions->keyBy('name');

        $adminRole->permissions()->sync($permissions->pluck('id')->all());
        $cashierRole->permissions()->sync(
            $permissionIds->only([
                'dashboard.view',
                'customers.manage',
                'sales.manage',
                'payments.manage',
                'stocks.view',
            ])->pluck('id')->all()
        );
        $pharmacistRole->permissions()->sync(
            $permissionIds->only([
                'dashboard.view',
                'products.manage',
                'stocks.view',
                'sales.manage',
                'prescriptions.manage',
            ])->pluck('id')->all()
        );

        $main = Branch::firstOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'Main Pharmacy',
                'phone' => '+855 12 345 678',
                'email' => 'main@pharmacy.local',
                'city' => 'Phnom Penh',
                'country' => 'Cambodia',
                'is_main' => true,
                'is_active' => true,
            ]
        );

        Branch::firstOrCreate(
            ['code' => 'BR-01'],
            [
                'name' => 'Branch 01',
                'phone' => '+855 12 000 000',
                'email' => 'br01@pharmacy.local',
                'city' => 'Siem Reap',
                'country' => 'Cambodia',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@pharmacy.local'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'branch_id' => $main->id,
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]
        );

        foreach ([
            ['name' => 'Antibiotic', 'slug' => 'antibiotic'],
            ['name' => 'Pain Reliever', 'slug' => 'pain-reliever'],
            ['name' => 'Vitamin', 'slug' => 'vitamin'],
            ['name' => 'Cold & Flu', 'slug' => 'cold-flu'],
        ] as $row) {
            Category::firstOrCreate(['slug' => $row['slug']], $row);
        }

        foreach ([
            ['name' => 'Piece', 'symbol' => 'pcs'],
            ['name' => 'Box', 'symbol' => 'box'],
            ['name' => 'Bottle', 'symbol' => 'btl'],
            ['name' => 'Milliliter', 'symbol' => 'ml'],
        ] as $row) {
            Unit::firstOrCreate(['symbol' => $row['symbol']], $row);
        }

        Manufacturer::firstOrCreate(['name' => 'Generic Pharma Co.'], ['country' => 'Cambodia']);
        Manufacturer::firstOrCreate(['name' => 'Acme Pharmaceuticals'], ['country' => 'Vietnam']);

        Supplier::firstOrCreate(
            ['code' => 'SUP-001'],
            ['name' => 'MediWholesale Distribution', 'phone' => '+855 99 111 222']
        );

        Customer::firstOrCreate(
            ['code' => 'WALKIN'],
            ['name' => 'Walk-in Customer', 'is_active' => true]
        );

        foreach (['Rent', 'Utilities', 'Salaries', 'Maintenance'] as $name) {
            ExpenseCategory::firstOrCreate(['name' => $name]);
        }
    }
}
