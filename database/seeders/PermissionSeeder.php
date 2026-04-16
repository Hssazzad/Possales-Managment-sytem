<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // User Management
            ['name' => 'view-users', 'display_name' => 'View Users', 'description' => 'Can view user list'],
            ['name' => 'create-users', 'display_name' => 'Create Users', 'description' => 'Can create new users'],
            ['name' => 'edit-users', 'display_name' => 'Edit Users', 'description' => 'Can edit existing users'],
            ['name' => 'delete-users', 'display_name' => 'Delete Users', 'description' => 'Can delete users'],

            // Role Management
            ['name' => 'view-roles', 'display_name' => 'View Roles', 'description' => 'Can view role list'],
            ['name' => 'create-roles', 'display_name' => 'Create Roles', 'description' => 'Can create new roles'],
            ['name' => 'edit-roles', 'display_name' => 'Edit Roles', 'description' => 'Can edit existing roles'],
            ['name' => 'delete-roles', 'display_name' => 'Delete Roles', 'description' => 'Can delete roles'],

            // Permission Management
            ['name' => 'view-permissions', 'display_name' => 'View Permissions', 'description' => 'Can view permission list'],
            ['name' => 'create-permissions', 'display_name' => 'Create Permissions', 'description' => 'Can create new permissions'],
            ['name' => 'edit-permissions', 'display_name' => 'Edit Permissions', 'description' => 'Can edit existing permissions'],
            ['name' => 'delete-permissions', 'display_name' => 'Delete Permissions', 'description' => 'Can delete permissions'],

            // Menu Management
            ['name' => 'view-menus', 'display_name' => 'View Menus', 'description' => 'Can view menu list'],
            ['name' => 'create-menus', 'display_name' => 'Create Menus', 'description' => 'Can create new menus'],
            ['name' => 'edit-menus', 'display_name' => 'Edit Menus', 'description' => 'Can edit existing menus'],
            ['name' => 'delete-menus', 'display_name' => 'Delete Menus', 'description' => 'Can delete menus'],

            // Sales Management
            ['name' => 'view-sales', 'display_name' => 'View Sales', 'description' => 'Can view sales list'],
            ['name' => 'create-sales', 'display_name' => 'Create Sales', 'description' => 'Can create new sales'],
            ['name' => 'edit-sales', 'display_name' => 'Edit Sales', 'description' => 'Can edit existing sales'],
            ['name' => 'delete-sales', 'display_name' => 'Delete Sales', 'description' => 'Can delete sales'],

            // Purchase Management
            ['name' => 'view-purchases', 'display_name' => 'View Purchases', 'description' => 'Can view purchase list'],
            ['name' => 'create-purchases', 'display_name' => 'Create Purchases', 'description' => 'Can create new purchases'],
            ['name' => 'edit-purchases', 'display_name' => 'Edit Purchases', 'description' => 'Can edit existing purchases'],
            ['name' => 'delete-purchases', 'display_name' => 'Delete Purchases', 'description' => 'Can delete existing purchases'],

            // Product Management
            ['name' => 'view-products', 'display_name' => 'View Products', 'description' => 'Can view product list'],
            ['name' => 'create-products', 'display_name' => 'Create Products', 'description' => 'Can create new products'],
            ['name' => 'edit-products', 'display_name' => 'Edit Products', 'description' => 'Can edit existing products'],
            ['name' => 'delete-products', 'display_name' => 'Delete Products', 'description' => 'Can delete existing products'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
