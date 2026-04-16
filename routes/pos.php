<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PosController;

Route::middleware(['auth'])->group(function () {
    // Point of Sale Routes
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/search-products', [PosController::class, 'searchProducts'])->name('pos.search');
    Route::post('/pos/process-sale', [PosController::class, 'processSale'])->name('pos.process');
    Route::post('/pos/hold-sale', [PosController::class, 'holdSale'])->name('pos.hold');
    Route::get('/pos/held-sales', [PosController::class, 'getHeldSales'])->name('pos.held');
    Route::get('/pos/resume/{holdId}', [PosController::class, 'resumeSale'])->name('pos.resume');
    Route::get('/pos/stats', [PosController::class, 'getTodayStats'])->name('pos.stats');
    Route::post('/pos/open-cash-drawer', [PosController::class, 'openCashDrawer'])->name('pos.cash-drawer');
    Route::get('/pos/receipt/{saleId}', [PosController::class, 'generateReceipt'])->name('pos.receipt');
});
