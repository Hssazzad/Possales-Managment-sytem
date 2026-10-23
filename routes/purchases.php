<?php

use App\Http\Controllers\Admin\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    // Purchase Management
    Route::get('/newpurchase', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::get('/purchaselist', [PurchaseController::class, 'index'])->name('purchases.list');
    Route::get('/admin/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::post('/admin/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/admin/purchases/{id}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::get('/admin/purchases/{id}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
    Route::put('/admin/purchases/{id}', [PurchaseController::class, 'update'])->name('purchases.update');
    Route::delete('/admin/purchases/{id}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');

});
