<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use Carbon\Carbon;

class ProfitLossController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Get date range from request or default to current year
        $year = request('year', Carbon::now()->year);
        $month = request('month'); // Optional month filter

        // Build query based on filters
        $salesQuery = Sale::with(['items.product', 'customer'])
            ->whereYear('created_at', $year);

        $purchaseQuery = Purchase::with(['supplier'])
            ->whereYear('created_at', $year);

        $expenseQuery = Expense::whereYear('expense_date', $year);

        if ($month) {
            $salesQuery->whereMonth('created_at', $month);
            $purchaseQuery->whereMonth('created_at', $month);
            $expenseQuery->whereMonth('expense_date', $month);
        }

        // Get data
        $sales = $salesQuery->get();
        $purchases = $purchaseQuery->get();
        $expenses = $expenseQuery->get();

        // Calculate totals
        $totalSales = $sales->sum('total_amount');
        $totalPurchases = $purchases->sum('total_amount');
        $totalExpenses = $expenses->sum('amount');

        // Calculate profit/loss per sale
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
                'customer_id' => $sale->customer_id,
                'customer' => $sale->customer_name ?? 'Walk-in Customer',
                'sales_amount' => $sale->total_amount,
                'cost' => $saleCost,
                'profit' => $profit,
                'type' => $profit >= 0 ? 'profit' : 'loss',
                'payment_status' => $sale->payment_status,
            ];

            if ($profit >= 0) {
                $totalProfit += $profit;
            } else {
                $totalLoss += abs($profit);
            }
        }

        // Net profit/loss
        $netProfitLoss = $totalProfit - $totalLoss - $totalExpenses;

        // Get monthly breakdown
        $monthlyBreakdown = $this->getMonthlyBreakdown($year, $month);

        // Get product-wise profit/loss
        $productWiseProfitLoss = $this->getProductWiseProfitLoss($sales);

        return view('admin.profit-loss.index', compact(
            'totalSales',
            'totalPurchases',
            'totalExpenses',
            'totalProfit',
            'totalLoss',
            'netProfitLoss',
            'profitLossDetails',
            'monthlyBreakdown',
            'productWiseProfitLoss',
            'year',
            'month'
        ));
    }

    private function getMonthlyBreakdown($year, $filterMonth = null)
    {
        $months = [];

        if ($filterMonth) {
            // Daily breakdown for specific month
            $daysInMonth = Carbon::create($year, $filterMonth, 1)->daysInMonth;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($year, $filterMonth, $day);
                $daySales = Sale::whereDate('created_at', $date)->get();
                $dayPurchases = Purchase::whereDate('created_at', $date)->get();
                $dayExpenses = Expense::whereDate('expense_date', $date)->get();

                $dayProfit = 0;
                foreach ($daySales as $sale) {
                    $saleCost = 0;
                    foreach ($sale->items as $item) {
                        $product = $item->product;
                        if ($product) {
                            $saleCost += $product->purchase_price * $item->quantity;
                        }
                    }
                    $dayProfit += ($sale->total_amount - $saleCost);
                }

                $months[] = [
                    'period' => $day,
                    'sales' => $daySales->sum('total_amount'),
                    'purchases' => $dayPurchases->sum('total_amount'),
                    'expenses' => $dayExpenses->sum('amount'),
                    'profit' => $dayProfit,
                    'net' => $dayProfit - $dayExpenses->sum('amount'),
                ];
            }
        } else {
            // Monthly breakdown for year
            for ($month = 1; $month <= 12; $month++) {
                $monthSales = Sale::whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->get();
                $monthPurchases = Purchase::whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->get();
                $monthExpenses = Expense::whereMonth('expense_date', $month)
                    ->whereYear('expense_date', $year)
                    ->get();

                $monthProfit = 0;
                foreach ($monthSales as $sale) {
                    $saleCost = 0;
                    foreach ($sale->items as $item) {
                        $product = $item->product;
                        if ($product) {
                            $saleCost += $product->purchase_price * $item->quantity;
                        }
                    }
                    $monthProfit += ($sale->total_amount - $saleCost);
                }

                $months[] = [
                    'period' => Carbon::create()->month($month)->format('M'),
                    'sales' => $monthSales->sum('total_amount'),
                    'purchases' => $monthPurchases->sum('total_amount'),
                    'expenses' => $monthExpenses->sum('amount'),
                    'profit' => $monthProfit,
                    'net' => $monthProfit - $monthExpenses->sum('amount'),
                ];
            }
        }

        return $months;
    }

    private function getProductWiseProfitLoss($sales)
    {
        $productStats = [];

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $product = $item->product;
                if (! $product) {
                    continue;
                }

                $productId = $product->id;
                $productName = $product->name;
                $saleAmount = $item->quantity * $item->price;
                $cost = $item->quantity * $product->purchase_price;
                $profit = $saleAmount - $cost;

                if (! isset($productStats[$productId])) {
                    $productStats[$productId] = [
                        'name' => $productName,
                        'total_sales' => 0,
                        'total_cost' => 0,
                        'total_profit' => 0,
                        'quantity_sold' => 0,
                    ];
                }

                $productStats[$productId]['total_sales'] += $saleAmount;
                $productStats[$productId]['total_cost'] += $cost;
                $productStats[$productId]['total_profit'] += $profit;
                $productStats[$productId]['quantity_sold'] += $item->quantity;
            }
        }

        return array_values($productStats);
    }
}
