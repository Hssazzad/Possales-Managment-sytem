<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Sale;
use Illuminate\Http\Request;

class LossProfitReportController extends Controller
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
        $profitLossData = [];
        $totalSales = 0;
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

            $profitLossData[] = [
                'date' => $sale->created_at->format('Y-m-d'),
                'invoice' => $sale->invoice_number,
                'customer' => $sale->customer_name ?? 'Walk-in Customer',
                'customer_id' => $sale->customer_id,
                'sales_amount' => $sale->total_amount,
                'cost' => $saleCost,
                'profit' => $profit,
                'type' => $profit >= 0 ? 'profit' : 'loss',
                'payment_status' => $sale->payment_status,
            ];

            $totalSales += $sale->total_amount;

            if ($profit >= 0) {
                $totalProfit += $profit;
            } else {
                $totalLoss += abs($profit);
            }
        }

        // Get expenses for the period
        $expenseQuery = Expense::whereYear('expense_date', $year);

        if ($month) {
            $expenseQuery->whereMonth('expense_date', $month);
        }

        $expenses = $expenseQuery->get();
        $totalExpenses = $expenses->sum('amount');

        // Calculate net profit/loss
        $netProfitLoss = $totalProfit - ($totalLoss + $totalExpenses);

        // Monthly breakdown
        $monthlyData = [];
        foreach ($sales as $sale) {
            $monthYear = $sale->created_at->format('Y-m');
            if (! isset($monthlyData[$monthYear])) {
                $monthlyData[$monthYear] = [
                    'month' => $sale->created_at->format('F Y'),
                    'sales' => 0,
                    'profit' => 0,
                    'loss' => 0,
                ];
            }

            $saleCost = 0;
            foreach ($sale->items as $item) {
                $product = $item->product;
                if ($product) {
                    $saleCost += $product->purchase_price * $item->quantity;
                }
            }

            $profit = $sale->total_amount - $saleCost;
            $monthlyData[$monthYear]['sales'] += $sale->total_amount;

            if ($profit >= 0) {
                $monthlyData[$monthYear]['profit'] += $profit;
            } else {
                $monthlyData[$monthYear]['loss'] += abs($profit);
            }
        }

        // Add expenses to monthly data
        foreach ($expenses as $expense) {
            $monthYear = $expense->expense_date->format('Y-m');
            if (! isset($monthlyData[$monthYear])) {
                $monthlyData[$monthYear] = [
                    'month' => $expense->expense_date->format('F Y'),
                    'sales' => 0,
                    'profit' => 0,
                    'loss' => 0,
                ];
            }
            $monthlyData[$monthYear]['loss'] += $expense->amount;
        }

        ksort($monthlyData);
        $monthlyData = array_values($monthlyData);

        return view('admin.loss-profit-reports.index', compact(
            'profitLossData',
            'totalSales',
            'totalProfit',
            'totalLoss',
            'totalExpenses',
            'netProfitLoss',
            'monthlyData',
            'year',
            'month'
        ));
    }
}
