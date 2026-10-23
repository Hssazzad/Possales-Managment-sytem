<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Expense::with(['expenseCategory', 'user'])
            ->orderBy('expense_date', 'desc');

        // Filter by date range if provided
        if ($request->filled('start_date')) {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }

        // Filter by category if provided
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by payment method if provided
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $expenses = $query->get();

        // Calculate totals
        $totalExpenses = $expenses->sum('amount');
        $totalExpenseCount = $expenses->count();

        // Group by category for summary
        $categoryBreakdown = $expenses->groupBy('category')
            ->map(function ($expenses, $categoryId) {
                $category = $expenses->first()->expenseCategory;

                return [
                    'category_name' => $category->name ?? 'Unknown',
                    'total' => $expenses->sum('amount'),
                    'count' => $expenses->count(),
                ];
            })->sortByDesc('total');

        // Group by payment method
        $paymentMethodBreakdown = $expenses->groupBy('payment_method')
            ->map(function ($expenses, $method) {
                return [
                    'method' => $method ?: 'Other',
                    'total' => $expenses->sum('amount'),
                    'count' => $expenses->count(),
                ];
            })->sortByDesc('total');

        // Monthly breakdown
        $monthlyExpenses = [];
        foreach ($expenses as $expense) {
            $monthYear = $expense->expense_date->format('Y-m');
            if (! isset($monthlyExpenses[$monthYear])) {
                $monthlyExpenses[$monthYear] = [
                    'month' => $expense->expense_date->format('F Y'),
                    'total' => 0,
                    'count' => 0,
                ];
            }
            $monthlyExpenses[$monthYear]['total'] += $expense->amount;
            $monthlyExpenses[$monthYear]['count']++;
        }
        ksort($monthlyExpenses);
        $monthlyExpenses = array_values($monthlyExpenses);

        // Get all expense categories for filter
        $expenseCategories = ExpenseCategory::active()->get();

        // Get unique payment methods for filter
        $paymentMethods = Expense::distinct('payment_method')
            ->whereNotNull('payment_method')
            ->pluck('payment_method')
            ->sort();

        return view('admin.expense-reports.index', compact(
            'expenses',
            'totalExpenses',
            'totalExpenseCount',
            'categoryBreakdown',
            'paymentMethodBreakdown',
            'monthlyExpenses',
            'expenseCategories',
            'paymentMethods'
        ));
    }
}