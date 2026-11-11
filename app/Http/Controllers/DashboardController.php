<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Completed refunds reduce net sales (pending returns don't affect totals until approved)
        $completedRefunds = \App\Models\ReturnItem::whereIn('status', ['approved', 'completed'])->sum('refund_amount');
        $todayRefunds = \App\Models\ReturnItem::whereIn('status', ['approved', 'completed'])
            ->whereDate('return_date', today())->sum('refund_amount');

        // Get today's statistics (net of refunds)
        $todaySales = max(0, Sale::today()->sum('total_amount') - $todayRefunds);
        $yesterdaySales = Sale::whereDate('created_at', Carbon::yesterday())->sum('total_amount');
        $todaySalesChange = $yesterdaySales > 0 ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : 0;

        // Get total sales (net of completed refunds)
        $totalSales = max(0, Sale::sum('total_amount') - $completedRefunds);
        $lastMonthSales = Sale::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('total_amount');
        $thisMonthSales = Sale::thisMonth()->sum('total_amount');
        $monthlySalesChange = $lastMonthSales > 0 ? (($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100 : 0;

        // Get income and expense
        $totalIncome = $this->getTotalIncome();
        $totalExpense = Expense::sum('amount');
        $lastMonthExpense = Expense::whereMonth('expense_date', Carbon::now()->subMonth()->month)
            ->whereYear('expense_date', Carbon::now()->subMonth()->year)
            ->sum('amount');
        $thisMonthExpense = Expense::whereMonth('expense_date', Carbon::now()->month)
            ->whereYear('expense_date', Carbon::now()->year)
            ->sum('amount');
        $monthlyExpenseChange = $lastMonthExpense > 0 ? (($thisMonthExpense - $lastMonthExpense) / $lastMonthExpense) * 100 : 0;

        // Get total purchase
        $totalPurchase = Purchase::sum('total_amount');

        // Get recent sales and purchases
        $recentSales = Sale::with('customer')
            ->latest()
            ->take(5)
            ->get();

        $recentPurchases = Purchase::with('supplier')
            ->latest()
            ->take(5)
            ->get();

        // Get monthly data for charts
        $monthlyData = $this->getMonthlyData();

        // Get counts
        $totalCustomers = Customer::count();
        $totalSuppliers = Supplier::count();
        $totalProducts = Product::count();

        $customerDue = (float) Customer::sum('due_amount');
        $supplierDue = (float) Supplier::sum('due_amount');
        $inventoryValue = (float) (Product::selectRaw('COALESCE(SUM(stock_quantity * purchase_price), 0) as total_value')->value('total_value') ?? 0);
        $lowStockCount = Product::lowStock()->count();
        $lowStockProducts = Product::lowStock()->with('category')->orderBy('stock_quantity', 'asc')->take(5)->get();
        $todaySalesCount = Sale::today()->count();
        $todayPurchase = (float) Purchase::whereDate('created_at', today())->sum('total_amount');
        $todayCollection = (float) Sale::today()->sum('paid_amount');

        return view('dashboard', compact(
            'todaySales',
            'todaySalesChange',
            'totalSales',
            'monthlySalesChange',
            'totalIncome',
            'totalExpense',
            'monthlyExpenseChange',
            'totalPurchase',
            'recentSales',
            'recentPurchases',
            'monthlyData',
            'totalCustomers',
            'totalSuppliers',
            'totalProducts',
            'customerDue',
            'supplierDue',
            'inventoryValue',
            'lowStockCount',
            'lowStockProducts',
            'todaySalesCount',
            'todayPurchase',
            'todayCollection'
        ));
    }

    private function getTotalIncome()
    {
        // Since there's no Income model, we'll calculate from sales profit
        // Fully returned sales contribute 0 profit; completed refunds reduce profit
        $totalProfit = 0;
        $sales = Sale::with('items.product')->get();

        foreach ($sales as $sale) {
            if ($sale->payment_status === 'returned') {
                continue;
            }
            // Net quantity after partial returns
            $totalCost = $sale->items->sum(function ($item) {
                $product = $item->product;
                $netQty = max(0, (int) $item->quantity - (int) ($item->returned_quantity ?? 0));

                return $product ? ($product->purchase_price * $netQty) : 0;
            });
            $netRevenue = $sale->total_amount;
            if ((int) $sale->items->sum('returned_quantity') > 0) {
                // Pro-rate revenue by remaining quantity for partial returns
                $soldQty = max(1, (int) $sale->items->sum('quantity'));
                $netQty = max(0, $soldQty - (int) $sale->items->sum('returned_quantity'));
                $netRevenue = $sale->total_amount * ($netQty / $soldQty);
            }
            $totalProfit += ($netRevenue - $totalCost);
        }

        return $totalProfit;
    }

    private function getMonthlyData()
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $currentYear = Carbon::now()->year;

        $salesData = [];
        $profitData = [];
        $lossData = [];
        $purchaseData = [];

        foreach ($months as $index => $month) {
            $monthNumber = $index + 1;

            // Sales data (net of completed refunds in that month)
            $monthlyGross = Sale::whereMonth('created_at', $monthNumber)
                ->whereYear('created_at', $currentYear)
                ->sum('total_amount');
            $monthlyRefunds = \App\Models\ReturnItem::whereIn('status', ['approved', 'completed'])
                ->whereMonth('return_date', $monthNumber)
                ->whereYear('return_date', $currentYear)
                ->sum('refund_amount');
            $salesData[] = max(0, $monthlyGross - $monthlyRefunds);

            // Purchase data
            $monthlyPurchase = Purchase::whereMonth('created_at', $monthNumber)
                ->whereYear('created_at', $currentYear)
                ->sum('total_amount');
            $purchaseData[] = $monthlyPurchase;

            // Calculate profit/loss (skip fully returned sales, pro-rate partials)
            $monthlyProfit = 0;
            $monthlySalesRecords = Sale::with('items.product')
                ->whereMonth('created_at', $monthNumber)
                ->whereYear('created_at', $currentYear)
                ->get();

            foreach ($monthlySalesRecords as $sale) {
                if ($sale->payment_status === 'returned') {
                    continue;
                }
                $totalCost = $sale->items->sum(function ($item) {
                    $product = $item->product;
                    $netQty = max(0, (int) $item->quantity - (int) ($item->returned_quantity ?? 0));

                    return $product ? ($product->purchase_price * $netQty) : 0;
                });
                $netRevenue = $sale->total_amount;
                if ((int) $sale->items->sum('returned_quantity') > 0) {
                    $soldQty = max(1, (int) $sale->items->sum('quantity'));
                    $netQty = max(0, $soldQty - (int) $sale->items->sum('returned_quantity'));
                    $netRevenue = $sale->total_amount * ($netQty / $soldQty);
                }
                $monthlyProfit += ($netRevenue - $totalCost);
            }

            if ($monthlyProfit >= 0) {
                $profitData[] = $monthlyProfit;
                $lossData[] = 0;
            } else {
                $profitData[] = 0;
                $lossData[] = abs($monthlyProfit);
            }
        }

        return [
            'labels' => $months,
            'sales' => $salesData,
            'profit' => $profitData,
            'loss' => $lossData,
            'purchase' => $purchaseData,
        ];
    }
}
