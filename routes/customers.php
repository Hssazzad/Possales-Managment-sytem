<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DueController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\SupplierPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    // Customer Management
    Route::get('/Customer', [DueController::class, 'customerIndex'])->name('customer.dues');
    Route::get('/createcustomer', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/admin/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/admin/customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::resource('admin/customers', CustomerController::class)->names('customers');

    // Supplier Management
    Route::get('/supplier', [DueController::class, 'supplierIndex'])->name('supplier.dues');
    Route::get('/addsupplier', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/admin/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/admin/suppliers/search', [SupplierController::class, 'search'])->name('suppliers.search');
    Route::get('/admin/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/admin/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::resource('admin/suppliers', SupplierController::class)->names('suppliers');

    // Supplier Payments
    Route::get('/supplier-payment', [SupplierPaymentController::class, 'index'])->name('supplier-payments.quick-index');
    Route::get('/supplier-payment/create', [SupplierPaymentController::class, 'create'])->name('supplier-payments.quick-create');
    Route::post('/supplier-payment', [SupplierPaymentController::class, 'store'])->name('supplier-payments.quick-store');
    Route::get('/admin/supplier-payments', [SupplierPaymentController::class, 'index'])->name('supplier-payments.index');
    Route::get('/admin/supplier-payments/create', [SupplierPaymentController::class, 'create'])->name('supplier-payments.create');
    Route::post('/admin/supplier-payments', [SupplierPaymentController::class, 'store'])->name('supplier-payments.store');
    Route::get('/admin/suppliers/{supplierId}/due-purchases', [SupplierPaymentController::class, 'duePurchases'])->name('supplier-payments.due-purchases');
    Route::get('/admin/supplier-payments/{supplierPayment}/invoice-pdf', [SupplierPaymentController::class, 'invoicePdf'])
        ->name('supplier-payments.invoice-pdf');

    // Dues Management
    Route::resource('dues', DueController::class)->names('dues');

});
