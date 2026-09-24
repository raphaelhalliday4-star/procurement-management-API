<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $roles = [
            'super-admin' => [ 
                'users.view', 
                'users.create', 
                'users.update', 
                'users.delete',
                'permissions.manage',

                'vendors.view', 
                'vendors.create', 
                'vendors.update', 
                'vendors.delete',

                'services.view',
                'services.create',
                'services.update',
                'services.delete',

                'requisitions.view', 
                'requisitions.create', 
                'requisitions.approve', 
                'requisitions.reject',

                'rfqs.view', 
                'rfqs.create', 
                'rfqs.publish', 
                'rfqs.close', 

                'quotations.view', 
                'quotations.submit', 
                'quotations.evaluate', 

                'purchase_orders.view', 
                'purchase_orders.create', 
                'purchase_orders.approve', 

                'goods_receipts.view',
                'goods_receipts.create',

                'invoices.view', 
                'invoices.create', 
                'invoices.approve', 

                'payments.view', 
                'payments.create', ],

            'admin' => [ 
                'users.view', 
                'users.create', 
                'users.update', 
                'users.delete',
                'permissions.manage',

                'vendors.view', 
                'vendors.create', 
                'vendors.update', 
                'vendors.delete',

                'services.view',
                'services.create',
                'services.update',
                'services.delete',

                'requisitions.view', 

                'rfqs.view',

                'quotations.view', 

                'purchase_orders.view',

                'services.view',
                'goods_receipts.view',
                 
                'invoices.view', 

                'payments.view', ],

            'procurement-officer' => [
                 'vendors.view',

                  'services.view',
                  'services.create',
                  'services.update',
                  'services.delete',

                  'requisitions.view',

                  'rfqs.view', 
                  'rfqs.create', 
                  'rfqs.publish', 
                  'rfqs.close',

                  'quotations.view', 
                  'quotations.evaluate',

                  'purchase_orders.view', 
                  'purchase_orders.create', ],

            'department-manager' => [
                 'requisitions.view',
                  'requisitions.create',
                   'requisitions.approve',
                    'requisitions.reject', ],

            'finance-officer' => [ 
            'purchase_orders.view',

             'invoices.view',
              'invoices.create',
               'invoices.approve',

                'payments.view',
                 'payments.create', ],

            'approver' => [ 
             'requisitions.view',
             'requisitions.approve',
              'requisitions.reject',

               'purchase_orders.view',
                'purchase_orders.approve',

                 'invoices.view',
                  'invoices.approve', ],

            'vendor' => [ 'rfqs.view',
                'quotations.view', 
                'quotations.submit',
                
                'invoices.view', 
                'invoices.create', ],
        ];

        foreach ($roles as $roleName => $permissions) { 
            $role = Role::firstOrCreate([ 
                'name' => $roleName, 'guard_name' => 'api', 
            ]); 
                $role->syncPermissions($permissions); 
            }
    }
}   
