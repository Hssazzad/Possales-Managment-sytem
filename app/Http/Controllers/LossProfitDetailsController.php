<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;

class LossProfitDetailsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month');

        // Get sales with profit/loss calculations
        $salesQuery = Sale::with(['customer', 'items.product'])
            ->whereYear('created_at', $year);

        if ($month) {
            $salesQuery->whereMonth('created_at', $month);
        }

        $sales = $salesQuery->get();

        // Calculate profit/loss for each sale
        $profitLossDetails = [];
        $totalProfit = 0;
        $totalLoss = 0;

        foreach ($sales as $sale) {
            $saleCost = 0;
            foreach ($sale->items as $item) {
                $product = $item->product;
                if ($product) {
                    $saleCost += $product->purchase_price * $item->quantity;
                }
            }

            $profit = $sale->total_amount - $saleCost;

            $profitLossDetails[] = [
                'date' => $sale->created_at->format('Y-m-d'),
                'invoice' => $sale->invoice_number,
                'customer' => $sale->customer_name ?? 'Walk-in Customer',
                'customer_id' => $sale->customer_id,
                'sales_amount' => $sale->total_amount,
                'cost' => $saleCost,
                'profit' => $profit,
                'type' => $profit >= 0 ? 'profit' : 'loss',
                'payment_status' => $sale->payment_status,
                'created_at' => $sale->created_at,
            ];

            if ($profit >= 0) {
                $totalProfit += $profit;
            } else {
                $totalLoss += abs($profit);
            }
        }

        // Get purchases for the period
        $purchasesQuery = Purchase::with(['supplier'])
            ->whereYear('created_at', $year);

        if ($month) {
            $purchasesQuery->whereMonth('created_at', $month);
        }

        $purchases = $purchasesQuery->get();
        $totalPurchases = $purchases->sum('total_amount');

        // Get expenses for the period
        $expensesQuery = Expense::with(['expenseCategory'])
            ->whereYear('expense_date', $year);

        if ($month) {
            $expensesQuery->whereMonth('expense_date', $month);
        }

        $expenses = $expensesQuery->get();
        $totalExpenses = $expenses->sum('amount');

        // Calculate net profit/loss
        $netProfitLoss = $totalProfit - ($totalLoss + $totalExpenses);

        return view('admin.loss-profit-details.index', compact(
            'profitLossDetails',
            'totalProfit',
            'totalLoss',
            'totalPurchases',
            'totalExpenses',
            'netProfitLoss',
            'year',
            'month'
        ));
    }
}
