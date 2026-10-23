<?php

use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\IncomeController;
use App\Http\Controllers\Admin\VatController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    // Income Management
    Route::get('/incomes', [IncomeController::class, 'index'])->name('income.index');
    Route::get('/income-categories', [IncomeController::class, 'categories'])->name('income.categories');
    Route::get('/income-categories/create', [IncomeController::class, 'create'])->name('income.categories.create');
    Route::post('/income-categories', [IncomeController::class, 'store'])->name('income.categories.store');
    Route::get('/income-categories/{id}/edit', [IncomeController::class, 'edit'])->name('income.categories.edit');
    Route::put('/income-categories/{id}', [IncomeController::class, 'update'])->name('income.categories.update');
    Route::delete('/income-categories/{id}', [IncomeController::class, 'destroy'])->name('income.categories.destroy');

    // Expense Management
    Route::resource('expenses', ExpenseController::class)->names('expenses');
    Route::get('/expense-categories', [ExpenseController::class, 'categories'])->name('expenses.categories');
    Route::get('/expense-categories/create', [ExpenseController::class, 'createCategory'])->name('expenses.categories.create');
    Route::post('/expense-categories', [ExpenseController::class, 'storeCategory'])->name('expenses.categories.store');
    Route::get('/expense-categories/{id}/edit', [ExpenseController::class, 'editCategory'])->name('expenses.categories.edit');
    Route::put('/expense-categories/{id}', [ExpenseController::class, 'updateCategory'])->name('expenses.categories.update');
    Route::delete('/expense-categories/{id}', [ExpenseController::class, 'destroyCategory'])->name('expenses.categories.destroy');

    // VAT Management
    Route::resource('vats', VatController::class)->names('vats');

});
