<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DueReportController;
use App\Http\Controllers\ExpenseReportController;
use App\Http\Controllers\ExpiredProductReportController;
use App\Http\Controllers\IncomeReportController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LossProfitDetailsController;
use App\Http\Controllers\LossProfitReportController;
use App\Http\Controllers\PaymentTypeController;
use App\Http\Controllers\ProfitLossController;
use App\Http\Controllers\PurchaseReportController;
use App\Http\Controllers\PurchaseReturnReportController;
use App\Http\Controllers\SaleReportController;
use App\Http\Controllers\SaleReturnReportController;
use App\Http\Controllers\StockReportController;
use App\Http\Controllers\SupplierDueReportController;
use App\Http\Controllers\TransactionHistoryReportController;
use App\Http\Controllers\VatReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;



Auth::routes();

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/loss-profits', [ProfitLossController::class, 'index'])->name('profit-loss.index');

    // Payment Types
    Route::get('/payment-types', [PaymentTypeController::class, 'index'])->name('payment-types.index');
    Route::post('/payment-types', [PaymentTypeController::class, 'store'])->name('payment-types.store');
    Route::put('/payment-types/{paymentType}', [PaymentTypeController::class, 'update'])->name('payment-types.update');
    Route::patch('/payment-types/{paymentType}/status', [PaymentTypeController::class, 'updateStatus'])->name('payment-types.updateStatus');
    Route::delete('/payment-types/{paymentType}', [PaymentTypeController::class, 'destroy'])->name('payment-types.destroy');

    // Sale Reports
    Route::get('/sale-reports', [SaleReportController::class, 'index'])->name('sale-reports.index');
    Route::get('/sale-reports/export', [SaleReportController::class, 'export'])->name('sale-reports.export');

    // Sale Return Reports
    Route::get('/sale-return-reports', [SaleReturnReportController::class, 'index'])->name('sale-return-reports.index');

    // Purchase Return Reports
    Route::get('/purchase-return-reports', [PurchaseReturnReportController::class, 'index'])->name('purchase-return-reports.index');

    // VAT Reports
    Route::get('/vat-reports', [VatReportController::class, 'index'])->name('vat-reports.index');

    // Income Reports
    Route::get('/income-reports', [IncomeReportController::class, 'index'])->name('income-reports.index');

    // Expense Reports
    Route::get('/expense-reports', [ExpenseReportController::class, 'index'])->name('expense-reports.index');

    // Loss/Profit Details
    Route::get('/loss-profits-details', [LossProfitDetailsController::class, 'index'])->name('loss-profit-details.index');

    // Stock Reports
    Route::get('/stock-reports', [StockReportController::class, 'index'])->name('stock-reports.index');

    // Due Reports
    Route::get('/due-reports', [DueReportController::class, 'index'])->name('due-reports.index');

    // Supplier Due Reports
    Route::get('/supplier-due-reports', [SupplierDueReportController::class, 'index'])->name('supplier-due-reports.index');

    // Transaction History Reports
    Route::get('/transaction-history-reports', [TransactionHistoryReportController::class, 'index'])->name('transaction-history-reports.index');

    // Expired Product Reports
    Route::get('/expired-product-reports', [ExpiredProductReportController::class, 'index'])->name('expired-product-reports.index');

    // Purchase Reports
    Route::get('/purchase-reports', [PurchaseReportController::class, 'index'])->name('purchase-reports.index');

    // Loss/Profit Reports
    Route::get('/loss-profit-reports', [LossProfitReportController::class, 'index'])->name('loss-profit-reports.index');

});

// Maintenance actions are intentionally limited to the super admin.
Route::middleware(['auth'])->prefix('system')->group(function () {
    Route::get('/clear-cache', function () {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        return view('admin.system.clear-cache');
    })->name('system.clear-cache');

    Route::post('/clear-cache', function () {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        Artisan::call('deployment:clear-cache');

        return redirect()->route('system.clear-cache')
            ->with('success', 'System cache cleared successfully.');
    })->name('system.clear-cache.run');
});

// Language switching
Route::get('language/{lang}', [LanguageController::class, 'switch'])->name('language.switch');

// Handle common 404 errors
Route::get('Pos', function () {
    return redirect('/pos');
});

// On-the-fly placeholder image (saves to disk on first request)
Route::get('/images/no-product.png', function () {
    $path = public_path('images/no-product.png');

    if (!file_exists($path)) {
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        // 1x1 transparent PNG (base64 decoded)
        $transparentPng = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII='
        );
        file_put_contents($path, $transparentPng);
    }

    return response()->file($path, ['Content-Type' => 'image/png']);
});

// Include modular route files
require __DIR__.'/admin.php';
require __DIR__.'/web_accounting.php';
require __DIR__.'/billing.php';
require __DIR__.'/customers.php';
require __DIR__.'/finance.php';
require __DIR__.'/pos.php';
require __DIR__.'/products.php';
require __DIR__.'/purchases.php';
require __DIR__.'/sales.php';
require __DIR__.'/stock.php';
