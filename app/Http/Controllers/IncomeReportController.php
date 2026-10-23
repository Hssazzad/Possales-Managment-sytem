<?php

namespace App\Http\Controllers;

use App\Models\IncomeCategory;
use App\Models\Sale;
use Illuminate\Http\Request;

class IncomeReportController extends Controller
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

        // Calculate income from sales
        $incomeReport = [];
        $totalIncome = 0;
        $totalSalesCount = 0;
        $monthlyIncome = [];

        foreach ($sales as $sale) {
            $incomeData = [
                'date' => $sale->created_at->format('Y-m-d'),
                'invoice' => $sale->invoice_number,
                'customer' => $sale->customer_name ?? 'Walk-in Customer',
                'customer_id' => $sale->customer_id,
                'amount' => $sale->total_amount,
                'paid_amount' => $sale->paid_amount,
                'payment_method' => $sale->payment_method ?? 'N/A',
                'payment_status' => $sale->payment_status,
                'type' => 'Sales Income',
                'category' => 'Sales',
            ];

            $incomeReport[] = $incomeData;
            $totalIncome += $sale->total_amount;
            $totalSalesCount++;

            // Group by month for monthly breakdown
            $monthYear = $sale->created_at->format('Y-m');
            if (! isset($monthlyIncome[$monthYear])) {
                $monthlyIncome[$monthYear] = [
                    'month' => $sale->created_at->format('F Y'),
                    'total' => 0,
                    'count' => 0,
                ];
            }
            $monthlyIncome[$monthYear]['total'] += $sale->total_amount;
            $monthlyIncome[$monthYear]['count']++;
        }

        // Sort monthly income by date
        ksort($monthlyIncome);
        $monthlyIncome = array_values($monthlyIncome);

        // Get income categories (for potential other income sources)
        $incomeCategories = IncomeCategory::active()->get();

        // Calculate additional metrics
        $averageSaleAmount = $totalSalesCount > 0 ? $totalIncome / $totalSalesCount : 0;
        $totalPaidAmount = $sales->sum('paid_amount');
        $totalDueAmount = $totalIncome - $totalPaidAmount;

        // Group income by payment method
        $paymentMethodBreakdown = $sales->groupBy('payment_method')
            ->map(function ($sales, $method) {
                return [
                    'method' => $method ?: 'Other',
                    'total' => $sales->sum('total_amount'),
                    'count' => $sales->count(),
                ];
            })->sortByDesc('total');

        return view('admin.income-reports.index', compact(
            'incomeReport',
            'totalIncome',
            'totalSalesCount',
            'totalPaidAmount',
            'totalDueAmount',
            'averageSaleAmount',
            'monthlyIncome',
            'incomeCategories',
            'paymentMethodBreakdown'
        ));
    }
}
