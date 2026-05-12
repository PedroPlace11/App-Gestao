<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Clients / Entities
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',

            // Suppliers
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',

            // Contacts
            'contacts.view',
            'contacts.create',
            'contacts.edit',
            'contacts.delete',

            // Articles
            'articles.view',
            'articles.create',
            'articles.edit',
            'articles.delete',

            // Proposals
            'proposals.view',
            'proposals.create',
            'proposals.edit',
            'proposals.delete',

            // Orders
            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.delete',

            // Invoices
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
            'invoices.pay',

            // Calendar
            'calendar.view',
            'calendar.create',
            'calendar.edit',
            'calendar.delete',

            // Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Permissions
            'permissions.view',
            'permissions.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions(
            Permission::whereNotIn('name', ['users.delete', 'permissions.manage'])->get()
        );

        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions(
            Permission::where('name', 'like', '%.view')->get()
        );

        $this->command->info('Permissions seeded: ' . count($permissions) . ' permissions created.');
        $this->command->info('Roles seeded: admin, manager, viewer.');
    }
}
