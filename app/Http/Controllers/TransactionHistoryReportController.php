<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Purchase;
use App\Models\ReturnItem;
use App\Models\Sale;
use Illuminate\Http\Request;

class TransactionHistoryReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Get sales transactions
        $salesQuery = Sale::with(['customer'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('start_date')) {
            $salesQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $salesQuery->whereDate('created_at', '<=', $request->end_date);
        }

        $sales = $salesQuery->get();

        // Get purchase transactions
        $purchaseQuery = Purchase::with(['supplier'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('start_date')) {
            $purchaseQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $purchaseQuery->whereDate('created_at', '<=', $request->end_date);
        }

        $purchases = $purchaseQuery->get();

        // Get expense transactions
        $expenseQuery = Expense::with(['expenseCategory'])
            ->orderBy('expense_date', 'desc');

        if ($request->filled('start_date')) {
            $expenseQuery->whereDate('expense_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $expenseQuery->whereDate('expense_date', '<=', $request->end_date);
        }

        $expenses = $expenseQuery->get();

        // Get return transactions
        $returnQuery = ReturnItem::with(['customer', 'product'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('start_date')) {
            $returnQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $returnQuery->whereDate('created_at', '<=', $request->end_date);
        }

        $returns = $returnQuery->get();

        // Combine all transactions
        $allTransactions = collect();

        // Add sales transactions
        foreach ($sales as $sale) {
            $allTransactions->push([
                'date' => $sale->created_at->format('Y-m-d'),
                'type' => 'Sale',
                'reference' => $sale->invoice_number,
                'party' => $sale->customer_name ?? 'Walk-in Customer',
                'party_id' => $sale->customer_id,
                'total_amount' => $sale->total_amount,
                'paid_amount' => $sale->paid_amount,
                'due_amount' => $sale->total_amount - $sale->paid_amount,
                'payment_status' => $sale->payment_status,
                'payment_method' => $sale->payment_method ?? 'N/A',
                'created_at' => $sale->created_at,
            ]);
        }

        // Add purchase transactions
        foreach ($purchases as $purchase) {
            $allTransactions->push([
                'date' => $purchase->created_at->format('Y-m-d'),
                'type' => 'Purchase',
                'reference' => $purchase->invoice_number,
                'party' => $purchase->supplier->name ?? 'Unknown Supplier',
                'party_id' => $purchase->supplier_id,
                'total_amount' => $purchase->total_amount,
                'paid_amount' => $purchase->paid_amount,
                'due_amount' => $purchase->total_amount - $purchase->paid_amount,
                'payment_status' => $purchase->payment_status,
                'payment_method' => $purchase->payment_method ?? 'N/A',
                'created_at' => $purchase->created_at,
            ]);
        }

        // Add expense transactions
        foreach ($expenses as $expense) {
            $allTransactions->push([
                'date' => $expense->expense_date->format('Y-m-d'),
                'type' => 'Expense',
                'reference' => $expense->receipt_number ?? 'EXP-'.$expense->id,
                'party' => $expense->expenseCategory->name ?? 'General',
                'party_id' => $expense->expense_category_id,
                'total_amount' => $expense->amount,
                'paid_amount' => $expense->amount, // Expenses are typically paid immediately
                'due_amount' => 0,
                'payment_status' => 'paid',
                'payment_method' => $expense->payment_method ?? 'N/A',
                'created_at' => $expense->expense_date,
            ]);
        }

        // Add return transactions
        foreach ($returns as $return) {
            $allTransactions->push([
                'date' => $return->created_at->format('Y-m-d'),
                'type' => 'Return',
                'reference' => 'RET-'.$return->id,
                'party' => $return->customer_name ?? 'Walk-in Customer',
                'party_id' => $return->customer_id,
                'total_amount' => $return->refund_amount,
                'paid_amount' => $return->refund_amount, // Returns are typically refunded immediately
                'due_amount' => 0,
                'payment_status' => 'refunded',
                'payment_method' => $return->refund_method ?? 'N/A',
                'created_at' => $return->created_at,
            ]);
        }

        // Sort all transactions by date
        $transactions = $allTransactions->sortByDesc('created_at')->values();

        // Calculate totals
        $totalTransactions = $transactions->count();
        $totalAmount = $transactions->sum('total_amount');
        $totalPaid = $transactions->sum('paid_amount');
        $totalDue = $transactions->sum('due_amount');

        // Group by type
        $typeBreakdown = $transactions->groupBy('type')
            ->map(function ($items, $type) {
                return [
                    'type' => $type,
                    'count' => $items->count(),
                    'total_amount' => $items->sum('total_amount'),
                    'paid_amount' => $items->sum('paid_amount'),
                    'due_amount' => $items->sum('due_amount'),
                ];
            })->sortByDesc('total_amount');

        // Group by payment status
        $statusBreakdown = $transactions->groupBy('payment_status')
            ->map(function ($items, $status) {
                return [
                    'status' => $status,
                    'count' => $items->count(),
                    'total_amount' => $items->sum('total_amount'),
                ];
            });

        return view('admin.transaction-history-reports.index', compact(
            'transactions',
            'totalTransactions',
            'totalAmount',
            'totalPaid',
            'totalDue',
            'typeBreakdown',
            'statusBreakdown'
        ));
    }
}
