<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        Menu::truncate();

        $dashboard = Menu::create([
            'text'       => 'Dashboard',
            'url'        => 'dashboard',
            'icon'       => 'fas fa-fw fa-tachometer-alt',
            'sort_order' => 1,
        ]);

        $sales = Menu::create([
            'text'       => 'Sales',
            'url'        => '#',
            'icon'       => 'fas fa-fw fa-shopping-cart',
            'sort_order' => 2,
        ]);

        $purchases = Menu::create([
            'text'       => 'Purchases',
            'url'        => '#',
            'icon'       => 'fas fa-fw fa-truck',
            'sort_order' => 3,
        ]);

        Menu::create(['text' => 'Products',    'url' => '#', 'icon' => 'fas fa-fw fa-box',            'sort_order' => 4]);
        Menu::create(['text' => 'Stock List',  'url' => '#', 'icon' => 'fas fa-fw fa-warehouse',      'sort_order' => 5]);
        $customers = Menu::create(['text' => 'Customers',   'url' => '#', 'icon' => 'fas fa-fw fa-users',          'sort_order' => 6]);
        $suppliers = Menu::create(['text' => 'Suppliers',   'url' => '#', 'icon' => 'fas fa-fw fa-industry',       'sort_order' => 7]);
        Menu::create(['text' => 'Incomes',     'url' => '#', 'icon' => 'fas fa-fw fa-money-bill-wave','sort_order' => 8]);
        Menu::create(['text' => 'Expenses',    'url' => '#', 'icon' => 'fas fa-fw fa-file-invoice',   'sort_order' => 9]);
        Menu::create(['text' => 'Tax Setting', 'url' => '#', 'icon' => 'fas fa-fw fa-percent',        'sort_order' => 10]);
        Menu::create(['text' => 'Due List',    'url' => '#', 'icon' => 'fas fa-fw fa-clock',          'sort_order' => 11]);
        $settings = Menu::create(['text' => 'Settings',    'url' => '#', 'icon' => 'fas fa-fw fa-cog',            'sort_order' => 12]);

        // ABR Menu
        $abr = Menu::create(['text' => 'ABR', 'url' => '#', 'icon' => 'fas fa-fw fa-wine-glass-alt', 'sort_order' => 8]);

        // ABR submenu
        Menu::create(['text' => 'Alcohol', 'url' => '#', 'icon' => 'fas fa-fw fa-glass-cheers', 'parent_id' => $abr->id, 'sort_order' => 1]);
        Menu::create(['text' => 'Beverages', 'url' => '#', 'icon' => 'fas fa-fw fa-coffee', 'parent_id' => $abr->id, 'sort_order' => 2]);
        Menu::create(['text' => 'Restaurant', 'url' => '#', 'icon' => 'fas fa-fw fa-utensils', 'parent_id' => $abr->id, 'sort_order' => 3]);

        // Settings submenu
        Menu::create(['text' => 'Menu Management', 'url' => 'admin/menus', 'icon' => 'fas fa-fw fa-bars', 'parent_id' => $settings->id, 'sort_order' => 1]);
        Menu::create(['text' => 'User Management', 'url' => 'admin/users', 'icon' => 'fas fa-fw fa-users', 'parent_id' => $settings->id, 'sort_order' => 2]);
        Menu::create(['text' => 'Role Management', 'url' => 'admin/roles', 'icon' => 'fas fa-fw fa-user-tag', 'parent_id' => $settings->id, 'sort_order' => 3]);
        Menu::create(['text' => 'Permission Management', 'url' => 'admin/permissions', 'icon' => 'fas fa-fw fa-key', 'parent_id' => $settings->id, 'sort_order' => 4]);
        Menu::create(['text' => 'Assign Roles & Menus', 'url' => 'admin/users/assign-roles', 'icon' => 'fas fa-fw fa-user-cog', 'parent_id' => $settings->id, 'sort_order' => 5]);
        Menu::create(['text' => 'Add New Menu', 'url' => 'admin/menus/create', 'icon' => 'fas fa-fw fa-plus-circle', 'parent_id' => $settings->id, 'sort_order' => 6]);

        // Sales submenu
        Menu::create(['text' => 'Point of Sale', 'url' => 'pos', 'icon' => 'fas fa-fw fa-cash-register', 'parent_id' => $sales->id, 'sort_order' => 1]);
        Menu::create(['text' => 'New Sale',  'url' => '#', 'icon' => 'fas fa-fw fa-plus', 'parent_id' => $sales->id, 'sort_order' => 2]);
        Menu::create(['text' => 'Sale List', 'url' => '#', 'icon' => 'fas fa-fw fa-list', 'parent_id' => $sales->id, 'sort_order' => 3]);

        // Customers submenu
        Menu::create(['text' => 'Create Customer',  'url' => 'createcustomer', 'icon' => 'fas fa-fw fa-user-plus', 'parent_id' => $customers->id, 'sort_order' => 1]);
        Menu::create(['text' => 'Customer List',    'url' => 'admin/customers', 'icon' => 'fas fa-fw fa-list', 'parent_id' => $customers->id, 'sort_order' => 2]);

        // Suppliers submenu
        Menu::create(['text' => 'Create Supplier',  'url' => 'addsupplier', 'icon' => 'fas fa-fw fa-user-plus', 'parent_id' => $suppliers->id, 'sort_order' => 1]);
        Menu::create(['text' => 'Supplier List',    'url' => 'admin/suppliers', 'icon' => 'fas fa-fw fa-list', 'parent_id' => $suppliers->id, 'sort_order' => 2]);

        // Purchases submenu
        Menu::create(['text' => 'New Purchase',  'url' => '#', 'icon' => 'fas fa-fw fa-plus', 'parent_id' => $purchases->id, 'sort_order' => 1]);
        Menu::create(['text' => 'Purchase List', 'url' => '#', 'icon' => 'fas fa-fw fa-list', 'parent_id' => $purchases->id, 'sort_order' => 2]);
    }
}
