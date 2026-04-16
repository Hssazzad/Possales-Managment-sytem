<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $superAdmin = Role::create([
            'name' => 'super-admin',
            'display_name' => 'Super Admin',
            'description' => 'Full system access'
        ]);

        $admin = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Administrative access'
        ]);

        $manager = Role::create([
            'name' => 'manager',
            'display_name' => 'Manager',
            'description' => 'Management access'
        ]);

        $salesperson = Role::create([
            'name' => 'salesperson',
            'display_name' => 'Salesperson',
            'description' => 'Sales access'
        ]);

        $cashier = Role::create([
            'name' => 'cashier',
            'display_name' => 'Cashier',
            'description' => 'Cashier access'
        ]);

        // Get all permissions
        $permissions = Permission::all();

        // Super Admin gets all permissions
        $superAdmin->permissions()->sync($permissions->pluck('id'));

        // Admin permissions (excluding user management)
        $admin->permissions()->sync(
            $permissions->whereNotIn('name', [
                'view-users', 'create-users', 'edit-users', 'delete-users',
                'view-roles', 'create-roles', 'edit-roles', 'delete-roles',
                'view-permissions', 'create-permissions', 'edit-permissions', 'delete-permissions'
            ])->pluck('id')
        );

        // Manager permissions
        $manager->permissions()->sync(
            $permissions->whereIn('name', [
                'view-sales', 'create-sales', 'edit-sales',
                'view-purchases', 'create-purchases', 'edit-purchases',
                'view-products', 'create-products', 'edit-products',
                'view-menus'
            ])->pluck('id')
        );

        // Salesperson permissions
        $salesperson->permissions()->sync(
            $permissions->whereIn('name', [
                'view-sales', 'create-sales', 'edit-sales',
                'view-products',
                'view-menus'
            ])->pluck('id')
        );

        // Cashier permissions
        $cashier->permissions()->sync(
            $permissions->whereIn('name', [
                'view-sales', 'create-sales',
                'view-products',
                'view-menus'
            ])->pluck('id')
        );
    }
}
