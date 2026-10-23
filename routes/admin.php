<?php

use App\Http\Controllers\Admin\MenuManagerController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Menu Management
    Route::get('mainmenu', [MenuManagerController::class, 'parents'])->name('mainmenu.index');
    Route::post('mainmenu', [MenuManagerController::class, 'storeParent'])->name('mainmenu.store');
    Route::put('mainmenu/{parentMenu}', [MenuManagerController::class, 'updateParent'])->name('mainmenu.update');
    Route::delete('mainmenu/{parentMenu}', [MenuManagerController::class, 'destroyParent'])->name('mainmenu.destroy');
    Route::get('submenus', [MenuManagerController::class, 'submenus'])->name('submenus.index');
    Route::post('submenus', [MenuManagerController::class, 'storeSubmenu'])->name('submenus.store');
    Route::put('submenus/{subMenu}', [MenuManagerController::class, 'updateSubmenu'])->name('submenus.update');
    Route::delete('submenus/{subMenu}', [MenuManagerController::class, 'destroySubmenu'])->name('submenus.destroy');
    Route::get('admin/menus/submenus', [MenuManagerController::class, 'submenus']);
    Route::redirect('admin/menus', 'mainmenu')->name('menus.index');
    Route::redirect('admin/menus/create', 'submenus')->name('menus.create');

    // User Management
    Route::get('admin/users/assign-roles', [UserController::class, 'assignRoles'])->name('users.assign-roles');
    Route::get('admin/users/{user}/access', [UserController::class, 'getUserAccess'])->name('users.get-access');
    Route::post('admin/users/update-menu-access', [UserController::class, 'updateMenuAccess'])->name('users.update-menu-access');
    Route::resource('admin/users', UserController::class)->names('users');

    // Role Management
    Route::resource('admin/roles', RoleController::class)->names('roles');

    // Permission Management
    Route::resource('admin/permissions', PermissionController::class)->names('permissions');

});
