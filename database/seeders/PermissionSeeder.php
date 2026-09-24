<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $permissions = [
            // Users
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'permissions.manage',

            // Vendors
            'vendors.view',
            'vendors.create',
            'vendors.update',
            'vendors.delete',

            // Catalog
            'services.view',
            'services.create',
            'services.update',
            'services.delete',

            // Requisitions
            'requisitions.view',
            'requisitions.create',
            'requisitions.approve',
            'requisitions.reject',

            // RFQs
            'rfqs.view',
            'rfqs.create',
            'rfqs.publish',
            'rfqs.close',

            // Quotations
            'quotations.view',
            'quotations.submit',
            'quotations.evaluate',

            // Purchase Orders
            'purchase_orders.view',
            'purchase_orders.create',
            'purchase_orders.approve',

            // Goods receipts
            'goods_receipts.view',
            'goods_receipts.create',

            // Invoices
            'invoices.view',
            'invoices.create',
            'invoices.approve',

            // Payments
            'payments.view',
            'payments.create',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
            ]);
        }
    }
}
