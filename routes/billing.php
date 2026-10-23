<?php

use App\Http\Controllers\Billing\CreateInvoiceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    // Invoice Management
    Route::get('/invoice', [CreateInvoiceController::class, 'index'])->name('invoice.create');
    Route::get('/invoice/print/{billNo}', [CreateInvoiceController::class, 'printInvoice'])->name('invoice.print');

    // AJAX Endpoints
    Route::post('/invoice/get-main', [CreateInvoiceController::class, 'getMain'])->name('invoice.getMain');
    Route::post('/invoice/get-sub', [CreateInvoiceController::class, 'getSub'])->name('invoice.getSub');
    Route::post('/invoice/add-tmp', [CreateInvoiceController::class, 'addTmp'])->name('invoice.addTmp');
    Route::post('/invoice/get-tmp', [CreateInvoiceController::class, 'getTmp'])->name('invoice.getTmp');
    Route::post('/invoice/remove-tmp', [CreateInvoiceController::class, 'removeTmp'])->name('invoice.removeTmp');
    Route::post('/invoice/store', [CreateInvoiceController::class, 'store'])->name('invoice.store');
    Route::post('/invoice/search-patients', [CreateInvoiceController::class, 'searchPatients'])->name('invoice.searchPatients');
    Route::post('/invoice/get-patient', [CreateInvoiceController::class, 'getPatientDetails'])->name('invoice.getPatientDetails');

});
