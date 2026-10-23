<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\LabelPrintController;
use App\Http\Controllers\Admin\ModelController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RackController;
use App\Http\Controllers\Admin\ShelfController;
use App\Http\Controllers\Admin\UnitController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    // ─────────────────────────────────────────
    // Brand Management
    // ─────────────────────────────────────────
    Route::get('/admin/brands',          [BrandController::class, 'index'])->name('brands.index');
    Route::post('/admin/brands',         [BrandController::class, 'store'])->name('brands.store');
    Route::get('/admin/brands/{id}',     [BrandController::class, 'show'])->name('brands.show');
    Route::get('/admin/brands/{id}/edit',[BrandController::class, 'edit'])->name('brands.edit');
    Route::put('/admin/brands/{id}',     [BrandController::class, 'update'])->name('brands.update');
    Route::delete('/admin/brands/{id}',  [BrandController::class, 'destroy'])->name('brands.destroy');
    Route::get('/brand',    [BrandController::class, 'index'])->name('brands.list');
    Route::get('/addbrand', [BrandController::class, 'create'])->name('brands.create');

    // ─────────────────────────────────────────
    // Model Management
    // ─────────────────────────────────────────
    Route::get('/admin/models',          [ModelController::class, 'index'])->name('models.index');
    Route::post('/admin/models',         [ModelController::class, 'store'])->name('models.store');
    Route::get('/admin/models/{id}',     [ModelController::class, 'show'])->name('models.show');
    Route::get('/admin/models/{id}/edit',[ModelController::class, 'edit'])->name('models.edit');
    Route::put('/admin/models/{id}',     [ModelController::class, 'update'])->name('models.update');
    Route::delete('/admin/models/{id}',  [ModelController::class, 'destroy'])->name('models.destroy');
    Route::get('/model',    [ModelController::class, 'index'])->name('models.list');
    Route::get('/addmodel', [ModelController::class, 'create'])->name('models.create');

    // ─────────────────────────────────────────
    // Rack Management
    // ─────────────────────────────────────────
    Route::get('/admin/racks',          [RackController::class, 'index'])->name('racks.index');
    Route::post('/admin/racks',         [RackController::class, 'store'])->name('racks.store');
    Route::get('/admin/racks/{id}',     [RackController::class, 'show'])->name('racks.show');
    Route::get('/admin/racks/{id}/edit',[RackController::class, 'edit'])->name('racks.edit');
    Route::put('/admin/racks/{id}',     [RackController::class, 'update'])->name('racks.update');
    Route::delete('/admin/racks/{id}',  [RackController::class, 'destroy'])->name('racks.destroy');
    Route::get('/racks',   [RackController::class, 'index'])->name('racks.list');
    Route::get('/addrack', [RackController::class, 'create'])->name('racks.create');

    // ─────────────────────────────────────────
    // Shelf Management
    // ─────────────────────────────────────────
    Route::get('/admin/shelves',          [ShelfController::class, 'index'])->name('shelves.index');
    Route::post('/admin/shelves',         [ShelfController::class, 'store'])->name('shelves.store');
    Route::get('/admin/shelves/{id}',     [ShelfController::class, 'show'])->name('shelves.show');
    Route::get('/admin/shelves/{id}/edit',[ShelfController::class, 'edit'])->name('shelves.edit');
    Route::put('/admin/shelves/{id}',     [ShelfController::class, 'update'])->name('shelves.update');
    Route::delete('/admin/shelves/{id}',  [ShelfController::class, 'destroy'])->name('shelves.destroy');
    Route::get('/shelfs',   [ShelfController::class, 'index'])->name('shelves.list');
    Route::get('/addshelf', [ShelfController::class, 'create'])->name('shelves.create');

    // ─────────────────────────────────────────
    // Unit Management
    // ─────────────────────────────────────────
    Route::get('/admin/units',          [UnitController::class, 'index'])->name('units.index');
    Route::post('/admin/units',         [UnitController::class, 'store'])->name('units.store');
    Route::get('/admin/units/{id}',     [UnitController::class, 'show'])->name('units.show');
    Route::get('/admin/units/{id}/edit',[UnitController::class, 'edit'])->name('units.edit');
    Route::put('/admin/units/{id}',     [UnitController::class, 'update'])->name('units.update');
    Route::delete('/admin/units/{id}',  [UnitController::class, 'destroy'])->name('units.destroy');
    Route::get('/unit',    [UnitController::class, 'index'])->name('units.list');
    Route::get('/addunit', [UnitController::class, 'create'])->name('units.create');

    // ─────────────────────────────────────────
    // Category Management
    // ─────────────────────────────────────────
    Route::get('/admin/categories',                        [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/admin/categories',                       [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/admin/categories/{id}',                   [CategoryController::class, 'show'])->name('categories.show');
    Route::put('/admin/categories/{id}',                   [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/admin/categories/{id}',                [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/admin/categories/{id}/variations',        [CategoryController::class, 'getVariations'])->name('categories.variations');
    Route::get('/category',    [CategoryController::class, 'index'])->name('categories.list');
    Route::get('/addcategory', [CategoryController::class, 'index'])->name('categories.create');

    // ─────────────────────────────────────────
    // Product Management
    // ─────────────────────────────────────────
    Route::get('/admin/products',            [ProductController::class, 'index'])->name('products.index');
    Route::post('/admin/products',           [ProductController::class, 'store'])->name('products.store');
    // ✅ Static routes — {id} এর আগে
    Route::get('/admin/products/search',     [ProductController::class, 'search'])->name('products.search');
    Route::get('/admin/products/pos-search', [ProductController::class, 'posSearch'])->name('products.pos-search');
    Route::get('/admin/products/export',     [ProductController::class, 'export'])->name('products.export');
    Route::get('/admin/products/{id}',       [ProductController::class, 'show'])->name('products.show');
    Route::get('/admin/products/{id}/edit',  [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/admin/products/{id}',       [ProductController::class, 'update'])->name('products.update');
    Route::delete('/admin/products/{id}',    [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/get-barcode/{code}',        [ProductController::class, 'getBarcodeSvg'])->name('barcode.ajax');
    Route::get('/addproduct',       [ProductController::class, 'create'])->name('products.create');
    Route::get('/productlist',      [ProductController::class, 'index'])->name('products.list');
    Route::get('/allproduct',       [ProductController::class, 'index'])->name('products.all');
    Route::get('/expiredproduct',   [ProductController::class, 'expired'])->name('products.expired');
    Route::get('/expired-products', [ProductController::class, 'expired'])->name('products.expired-alt');

    // ─────────────────────────────────────────
    // Bulk Product Upload
    // ─────────────────────────────────────────
    Route::get('/bulk',                  [ProductController::class, 'bulkUpload'])->name('products.bulk');
    Route::get('/bulk/download-sample',  [ProductController::class, 'downloadSample'])->name('products.download-sample');
    Route::post('/bulk/upload',          [ProductController::class, 'processBulkUpload'])->name('products.bulk-upload');

    // ─────────────────────────────────────────
    // Label Printing
    // ─────────────────────────────────────────
    Route::get('/printlabels/search',    [LabelPrintController::class, 'search'])->name('labels.search');
    Route::post('/printlabels/generate', [LabelPrintController::class, 'generate'])->name('labels.generate');
    Route::get('/printlabels',           [LabelPrintController::class, 'index'])->name('labels.print');

    // ─────────────────────────────────────────
    // Color Management
    // ─────────────────────────────────────────
    Route::get('/admin/colors',          [ColorController::class, 'index'])->name('colors.index');
    Route::post('/admin/colors',         [ColorController::class, 'store'])->name('colors.store');
    Route::get('/admin/colors/{id}',     [ColorController::class, 'edit'])->name('colors.edit');
    Route::put('/admin/colors/{id}',     [ColorController::class, 'update'])->name('colors.update');
    Route::delete('/admin/colors/{id}',  [ColorController::class, 'destroy'])->name('colors.destroy');
    Route::get('/color',    [ColorController::class, 'index'])->name('colors.list');

    // ─────────────────────────────────────────
    // Size Management
    // ─────────────────────────────────────────
    Route::get('/admin/sizes',          [SizeController::class, 'index'])->name('sizes.index');
    Route::post('/admin/sizes',         [SizeController::class, 'store'])->name('sizes.store');
    Route::get('/admin/sizes/{id}',     [SizeController::class, 'edit'])->name('sizes.edit');
    Route::put('/admin/sizes/{id}',     [SizeController::class, 'update'])->name('sizes.update');
    Route::delete('/admin/sizes/{id}',  [SizeController::class, 'destroy'])->name('sizes.destroy');
    Route::patch('/admin/sizes/{id}/toggle-status', [SizeController::class, 'toggleStatus'])->name('sizes.toggle-status');
    Route::get('/size',    [SizeController::class, 'index'])->name('sizes.list');

});
