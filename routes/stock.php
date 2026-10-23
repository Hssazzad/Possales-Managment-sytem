<?php

use App\Http\Controllers\Admin\StockController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    // Stock Management
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stocklist', [StockController::class, 'index'])->name('stock.list');
    Route::get('/lowstock', [StockController::class, 'lowStock'])->name('stock.low');
    Route::get('/lowstock/export', [StockController::class, 'lowStockExport'])->name('stock.lowstock.export');
    Route::post('/admin/stock/update', [StockController::class, 'updateStock'])->name('stock.update');
});
