<?php
use App\Http\Controllers\Admin\ReturnsController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    // ==================== Sales Management ====================
    Route::get('/sales',                [SaleController::class, 'index'])  ->name('sales.index');
    Route::post('/sales',               [SaleController::class, 'index'])  ->name('sales.post');
    Route::get('/sales/search',         [SaleController::class, 'search']) ->name('sales.search');
    Route::get('/sales/{sale}/invoice-data', [SaleController::class, 'invoiceData'])->name('sales.invoice-data'); // ✅ Invoice modal
    Route::get('/sales/{sale}/edit-data',  [SaleController::class, 'editData']) ->name('sales.edit-data'); // ✅ Edit modal data
    Route::post('/sales/{sale}/update', [SaleController::class, 'update']) ->name('sales.update');   // ✅ Edit modal
    Route::post('/sales/{sale}/return', [SaleController::class, 'processReturn'])->name('sales.return.process'); // ✅ Return modal
    Route::get('/sales/{sale}',         [SaleController::class, 'show'])   ->name('sales.show');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
    Route::get('/sales/{sale}/invoice-pdf', [SaleController::class, 'invoicePdf'])->name('sales.invoice-pdf');
    Route::get('/sales/{sale}/delete', [SaleController::class, 'destroy'])->name('sales.destroy');
    Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy.delete');
    Route::get('/sales/bulk/delete',    [SaleController::class, 'bulkDestroy'])->name('sales.bulk-destroy');

    // ==================== Admin Search Routes ====================
    Route::prefix('admin')->group(function () {
        Route::get('/sales/search',     [SaleController::class,    'searchSales'])->name('admin.sales.search');
        Route::get('/customers/search', [CustomerController::class, 'search'])    ->name('admin.customers.search');
        Route::get('/products/search',  [ProductController::class,  'search'])    ->name('admin.products.search');
    });

    // ==================== Returns Management ====================
    Route::prefix('admin/returns')->name('returns.')->group(function () {
        Route::get('/',             [ReturnsController::class, 'index'])  ->name('index');
        Route::get('/export',       [ReturnsController::class, 'export']) ->name('export');
        Route::get('/create',       [ReturnsController::class, 'create']) ->name('create');
        Route::post('/',            [ReturnsController::class, 'store'])  ->name('store');
        Route::get('/{id}',         [ReturnsController::class, 'show'])   ->name('show');
        Route::get('/{id}/edit',    [ReturnsController::class, 'edit'])   ->name('edit');
        Route::put('/{id}',         [ReturnsController::class, 'update']) ->name('update');
        Route::patch('/{id}',       [ReturnsController::class, 'update'])->name('update.patch');
        Route::delete('/{id}',      [ReturnsController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/receipt', [ReturnsController::class, 'receipt'])->name('receipt');
    });

    // ==================== Legacy / Redirect Routes ====================
    Route::get('/sales-return', [ReturnsController::class, 'index']) ->name('sales.return');
    Route::get('/returnslist',  [ReturnsController::class, 'index']) ->name('returns.list');
    Route::get('/newreturn',    [ReturnsController::class, 'create'])->name('returns.new');
});