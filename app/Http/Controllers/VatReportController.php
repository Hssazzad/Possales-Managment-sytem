<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Vat;
use Illuminate\Http\Request;

class VatReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'items.product'])
            ->orderBy('created_at', 'desc');

        // Filter by date range if provided
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $sales = $query->get();

        // Get active VAT rates
        $vatRates = Vat::active()->get();

        // Calculate VAT from sales
        $vatReport = [];
        $totalVat = 0;
        $totalSalesWithVat = 0;
        $totalTaxableAmount = 0;

        foreach ($sales as $sale) {
            $vatAmount = 0;
            $taxableAmount = 0;
            $vatRate = 0;

            // Calculate VAT from sale items (assuming items have vat_amount field or we calculate it)
            foreach ($sale->items as $item) {
                // If there's a vat_amount field in sale_items
                if (isset($item->vat_amount)) {
                    $vatAmount += $item->vat_amount;
                    $taxableAmount += $item->total_price - $item->vat_amount;
                } else {
                    // Calculate VAT based on default rate (15%)
                    $defaultRate = 15; // Default VAT rate
                    $itemVat = ($item->total_price * $defaultRate) / 115; // Calculate VAT from inclusive price
                    $vatAmount += $itemVat;
                    $taxableAmount += $item->total_price - $itemVat;
                }
            }

            if ($vatAmount > 0) {
                $vatReport[] = [
                    'date' => $sale->created_at->format('Y-m-d'),
                    'invoice' => $sale->invoice_number,
                    'customer' => $sale->customer_name ?? 'Walk-in Customer',
                    'customer_id' => $sale->customer_id,
                    'total_amount' => $sale->total_amount,
                    'taxable_amount' => $taxableAmount,
                    'vat_amount' => $vatAmount,
                    'vat_rate' => $defaultRate ?? 15,
                    'payment_status' => $sale->payment_status,
                ];

                $totalVat += $vatAmount;
                $totalSalesWithVat++;
                $totalTaxableAmount += $taxableAmount;
            }
        }

        // Get VAT from purchases (input VAT)
        $purchaseVat = $this->calculatePurchaseVat($request);

        // Get VAT from expenses
        $expenseVat = $this->calculateExpenseVat($request);

        // Net VAT payable/receivable
        $netVat = $totalVat - ($purchaseVat['total_vat'] + $expenseVat['total_vat']);

        return view('admin.vat-reports.index', compact(
            'vatReport',
            'vatRates',
            'totalVat',
            'totalSalesWithVat',
            'totalTaxableAmount',
            'purchaseVat',
            'expenseVat',
            'netVat'
        ));
    }

    private function calculatePurchaseVat($request)
    {
        $query = Purchase::with(['supplier'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $purchases = $query->get();
        $totalVat = 0;
        $purchasesWithVat = [];

        foreach ($purchases as $purchase) {
            // Calculate VAT from purchases (assuming tax_amount field contains VAT)
            if ($purchase->tax_amount > 0) {
                $totalVat += $purchase->tax_amount;
                $purchasesWithVat[] = [
                    'date' => $purchase->created_at->format('Y-m-d'),
                    'invoice' => $purchase->invoice_number,
                    'supplier' => $purchase->supplier->name ?? 'Unknown',
                    'total_amount' => $purchase->total_amount,
                    'vat_amount' => $purchase->tax_amount,
                ];
            }
        }

        return [
            'purchases' => $purchasesWithVat,
            'total_vat' => $totalVat,
            'count' => count($purchasesWithVat),
        ];
    }

    private function calculateExpenseVat($request)
    {
        $query = Expense::with(['expenseCategory'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $expenses = $query->get();
        $totalVat = 0;
        $expensesWithVat = [];

        foreach ($expenses as $expense) {
            // Some expenses might have VAT (assuming amount includes VAT or there's a separate vat field)
            // For now, we'll assume 15% VAT on expenses that are marked as taxable
            $vatAmount = $expense->amount * 0.15; // 15% VAT
            $totalVat += $vatAmount;

            $expensesWithVat[] = [
                'date' => $expense->created_at->format('Y-m-d'),
                'category' => $expense->expenseCategory->name ?? 'General',
                'description' => $expense->description ?? 'N/A',
                'amount' => $expense->amount,
                'vat_amount' => $vatAmount,
            ];
        }

        return [
            'expenses' => $expensesWithVat,
            'total_vat' => $totalVat,
            'count' => count($expensesWithVat),
        ];
    }
}
