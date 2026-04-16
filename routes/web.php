<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\LabelPrintController;
use App\Http\Controllers\Admin\ModelController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RackController;
use App\Http\Controllers\Admin\ShelfController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\IncomeController;
use App\Http\Controllers\Admin\ReturnsController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\ProfileController;

Auth::routes();

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Point of Sale
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/search-products', [PosController::class, 'searchProducts'])->name('pos.search');
    Route::post('/pos/process-sale', [PosController::class, 'processSale'])->name('pos.process');
    Route::post('/pos/hold-sale', [PosController::class, 'holdSale'])->name('pos.hold');
    Route::get('/pos/held-sales', [PosController::class, 'getHeldSales'])->name('pos.held');
    Route::get('/pos/resume/{holdId}', [PosController::class, 'resumeSale'])->name('pos.resume');
    Route::get('/pos/stats', [PosController::class, 'getTodayStats'])->name('pos.stats');
    Route::post('/pos/open-cash-drawer', [PosController::class, 'openCashDrawer'])->name('pos.cash-drawer');
    Route::get('/pos/receipt/{saleId}', [PosController::class, 'generateReceipt'])->name('pos.receipt');
    Route::get('/admin/sales/search', [PosController::class, 'searchSales'])->name('sales.search');

    // Customer Management
    Route::get('/createcustomer', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/admin/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/admin/customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::resource('admin/customers', CustomerController::class)->names('customers');

    // Purchase Management
    Route::get('/newpurchase', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::get('/purchaselist', [PurchaseController::class, 'index'])->name('purchases.list');
    Route::get('/admin/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::post('/admin/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/admin/purchases/{id}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::get('/admin/purchases/{id}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
    Route::put('/admin/purchases/{id}', [PurchaseController::class, 'update'])->name('purchases.update');
    Route::delete('/admin/purchases/{id}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');

    // Returns Management
    Route::get('/newreturn', [ReturnsController::class, 'create'])->name('returns.create');
    Route::get('/returnslist', [ReturnsController::class, 'index'])->name('returns.list');
    Route::get('/admin/returns', [ReturnsController::class, 'index'])->name('returns.index');
    Route::post('/admin/returns', [ReturnsController::class, 'store'])->name('returns.store');
    Route::get('/admin/returns/{id}', [ReturnsController::class, 'show'])->name('returns.show');
    Route::get('/admin/returns/{id}/edit', [ReturnsController::class, 'edit'])->name('returns.edit');
    Route::put('/admin/returns/{id}', [ReturnsController::class, 'update'])->name('returns.update');
    Route::delete('/admin/returns/{id}', [ReturnsController::class, 'destroy'])->name('returns.destroy');

    // Brand Management
    Route::get('/brand', [BrandController::class, 'index'])->name('brands.list');
    Route::get('/addbrand', [BrandController::class, 'create'])->name('brands.create');
    Route::get('/admin/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::post('/admin/brands', [BrandController::class, 'store'])->name('brands.store');
    Route::get('/admin/brands/{id}', [BrandController::class, 'show'])->name('brands.show');
    Route::get('/admin/brands/{id}/edit', [BrandController::class, 'edit'])->name('brands.edit');
    Route::put('/admin/brands/{id}', [BrandController::class, 'update'])->name('brands.update');
    Route::delete('/admin/brands/{id}', [BrandController::class, 'destroy'])->name('brands.destroy');

    // Model Management
    Route::get('/model', [ModelController::class, 'index'])->name('models.list');
    Route::get('/addmodel', [ModelController::class, 'create'])->name('models.create');
    Route::get('/admin/models', [ModelController::class, 'index'])->name('models.index');
    Route::post('/admin/models', [ModelController::class, 'store'])->name('models.store');
    Route::get('/admin/models/{id}', [ModelController::class, 'show'])->name('models.show');
    Route::get('/admin/models/{id}/edit', [ModelController::class, 'edit'])->name('models.edit');
    Route::put('/admin/models/{id}', [ModelController::class, 'update'])->name('models.update');
    Route::delete('/admin/models/{id}', [ModelController::class, 'destroy'])->name('models.destroy');

    // Rack Management
    Route::get('/racks', [RackController::class, 'index'])->name('racks.list');
    Route::get('/addrack', [RackController::class, 'create'])->name('racks.create');
    Route::get('/admin/racks', [RackController::class, 'index'])->name('racks.index');
    Route::post('/admin/racks', [RackController::class, 'store'])->name('racks.store');
    Route::get('/admin/racks/{id}', [RackController::class, 'show'])->name('racks.show');
    Route::get('/admin/racks/{id}/edit', [RackController::class, 'edit'])->name('racks.edit');
    Route::put('/admin/racks/{id}', [RackController::class, 'update'])->name('racks.update');
    Route::delete('/admin/racks/{id}', [RackController::class, 'destroy'])->name('racks.destroy');

    // Shelf Management
    Route::get('/shelfs', [ShelfController::class, 'index'])->name('shelves.list');
    Route::get('/addshelf', [ShelfController::class, 'create'])->name('shelves.create');
    Route::get('/admin/shelves', [ShelfController::class, 'index'])->name('shelves.index');
    Route::post('/admin/shelves', [ShelfController::class, 'store'])->name('shelves.store');
    Route::get('/admin/shelves/{id}', [ShelfController::class, 'show'])->name('shelves.show');
    Route::get('/admin/shelves/{id}/edit', [ShelfController::class, 'edit'])->name('shelves.edit');
    Route::put('/admin/shelves/{id}', [ShelfController::class, 'update'])->name('shelves.update');
    Route::delete('/admin/shelves/{id}', [ShelfController::class, 'destroy'])->name('shelves.destroy');

    // Unit Management
    Route::get('/unit', [UnitController::class, 'index'])->name('units.list');
    Route::get('/addunit', [UnitController::class, 'create'])->name('units.create');
    Route::get('/admin/units', [UnitController::class, 'index'])->name('units.index');
    Route::post('/admin/units', [UnitController::class, 'store'])->name('units.store');
    Route::get('/admin/units/{id}', [UnitController::class, 'show'])->name('units.show');
    Route::get('/admin/units/{id}/edit', [UnitController::class, 'edit'])->name('units.edit');
    Route::put('/admin/units/{id}', [UnitController::class, 'update'])->name('units.update');
    Route::delete('/admin/units/{id}', [UnitController::class, 'destroy'])->name('units.destroy');

    // Category Management
    Route::get('/category', [CategoryController::class, 'index'])->name('categories.list');
    Route::get('/addcategory', [CategoryController::class, 'create'])->name('categories.create');
    Route::get('/admin/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/admin/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/admin/categories/{id}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/admin/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/admin/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/admin/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Product Management
    Route::get('/addproduct', [ProductController::class, 'create'])->name('products.create');
    Route::get('/productlist', [ProductController::class, 'index'])->name('products.list');
    Route::get('/allproduct', [ProductController::class, 'index'])->name('products.all');
    Route::get('/expiredproduct', [ProductController::class, 'expired'])->name('products.expired');
    Route::get('/expired-products', [ProductController::class, 'expired'])->name('products.expired-alt');
    Route::get('/admin/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/admin/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/admin/products/{id}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/admin/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/admin/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/admin/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/admin/products/search', [ProductController::class, 'search'])->name('products.search');
    Route::get('/admin/products/pos-search', [ProductController::class, 'posSearch'])->name('products.pos-search');

    // Bulk Product Upload
    Route::get('/bulk', [ProductController::class, 'bulkUpload'])->name('products.bulk');
    Route::get('/bulk/download-sample', [ProductController::class, 'downloadSample'])->name('products.download-sample');
    Route::post('/bulk/upload', [ProductController::class, 'processBulkUpload'])->name('products.bulk-upload');

    // Label Printing
    Route::get('/printlabels', [LabelPrintController::class, 'index'])->name('labels.print');
    Route::get('/printlabels/search', [LabelPrintController::class, 'search'])->name('labels.search');
    Route::post('/printlabels/generate', [LabelPrintController::class, 'generate'])->name('labels.generate');

    // Stock Management
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stocklist', [StockController::class, 'index'])->name('stock.list');
    Route::get('/lowstock', [StockController::class, 'lowStock'])->name('stock.low');

    // Income Management
    Route::get('/incomes', [IncomeController::class, 'index'])->name('income.index');
    Route::get('/income-categories', [IncomeController::class, 'categories'])->name('income.categories');
    Route::get('/income-categories/create', [IncomeController::class, 'create'])->name('income.categories.create');
    Route::post('/income-categories', [IncomeController::class, 'store'])->name('income.categories.store');
    Route::get('/income-categories/{id}/edit', [IncomeController::class, 'edit'])->name('income.categories.edit');
    Route::put('/income-categories/{id}', [IncomeController::class, 'update'])->name('income.categories.update');
    Route::delete('/income-categories/{id}', [IncomeController::class, 'destroy'])->name('income.categories.destroy');

    // Supplier Management
    Route::get('/addsupplier', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/admin/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/admin/suppliers/search', [SupplierController::class, 'search'])->name('suppliers.search');
    Route::get('/admin/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/admin/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::resource('admin/suppliers', SupplierController::class)->names('suppliers');

    // Menu Management
    Route::resource('admin/menus', MenuController::class)->names('menus');
    Route::post('admin/menus/{menu}/toggle', [MenuController::class, 'toggle'])->name('menus.toggle');
    Route::post('admin/menus/bulk-delete', [MenuController::class, 'bulkDelete'])->name('menus.bulk-delete');
    Route::get('admin/menus/get-next-sort-order', [MenuController::class, 'getNextSortOrder'])->name('menus.get-next-sort-order');

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

Route::get('language/{lang}', [LanguageController::class, 'switch'])->name('language.switch');

// Handle common 404 errors
Route::get('Pos', function() {
    return redirect('/pos');
});
