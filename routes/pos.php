<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PosController;

Route::middleware(['auth'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/process-sale', [PosController::class, 'processSale'])->name('pos.process');
    Route::get('/pos/add-product-by-barcode/{barcode}', [PosController::class, 'getProductByBarcode'])->name('pos.add-product-by-barcode');
    Route::post('/pos/scan-barcode', [PosController::class, 'scanByBarcode'])->name('pos.scan-barcode');
    Route::get('/pos/get-product/{barcode}', [PosController::class, 'getProductByBarcode'])->name('pos.get-product');
    Route::get('/admin/products/pos-search', [PosController::class, 'posSearch'])->name('pos.product-search');
    Route::get('/admin/products/search', [PosController::class, 'searchProductsLive'])->name('pos.products.search');
    Route::get('/admin/customers/search', [PosController::class, 'customerSearch'])->name('pos.customer-search');
    Route::post('/admin/customers/quick-store', [PosController::class, 'quickCustomerStore'])->name('pos.customer-quick-store');
    Route::get('/pos/receipt/{saleId}', [PosController::class, 'generateReceipt'])->name('pos.receipt');
});