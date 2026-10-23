<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;

class DueReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Sale::with(['customer'])
            ->orderBy('created_at', 'desc');

        // Filter by date range if provided
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $sales = $query->get();

        // Calculate due amounts
        $dueReport = [];
        $totalSalesDue = 0;
        $totalSalesAmount = 0;
        $totalPaidAmount = 0;

        foreach ($sales as $sale) {
            $dueAmount = $sale->total_amount - $sale->paid_amount;

            if ($dueAmount > 0) {
                $dueReport[] = [
                    'date' => $sale->created_at->format('Y-m-d'),
                    'invoice' => $sale->invoice_number,
                    'customer' => $sale->customer_name ?? 'Walk-in Customer',
                    'customer_id' => $sale->customer_id,
                    'total_amount' => $sale->total_amount,
                    'paid_amount' => $sale->paid_amount,
                    'due_amount' => $dueAmount,
                    'payment_status' => $sale->payment_status,
                    'type' => 'Sale',
                ];

                $totalSalesDue += $dueAmount;
                $totalSalesAmount += $sale->total_amount;
                $totalPaidAmount += $sale->paid_amount;
            }
        }

        // Get purchases with due amounts
        $purchaseQuery = Purchase::with(['supplier'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('start_date')) {
            $purchaseQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $purchaseQuery->whereDate('created_at', '<=', $request->end_date);
        }

        $purchases = $purchaseQuery->get();

        $totalPurchasesDue = 0;
        $totalPurchaseAmount = 0;
        $totalPurchasePaid = 0;

        foreach ($purchases as $purchase) {
            $dueAmount = $purchase->total_amount - $purchase->paid_amount;

            if ($dueAmount > 0) {
                $dueReport[] = [
                    'date' => $purchase->created_at->format('Y-m-d'),
                    'invoice' => $purchase->invoice_number,
                    'customer' => $purchase->supplier->name ?? 'Unknown Supplier',
                    'customer_id' => $purchase->supplier_id,
                    'total_amount' => $purchase->total_amount,
                    'paid_amount' => $purchase->paid_amount,
                    'due_amount' => $dueAmount,
                    'payment_status' => $purchase->payment_status,
                    'type' => 'Purchase',
                ];

                $totalPurchasesDue += $dueAmount;
                $totalPurchaseAmount += $purchase->total_amount;
                $totalPurchasePaid += $purchase->paid_amount;
            }
        }

        // Calculate totals
        $totalDue = $totalSalesDue + $totalPurchasesDue;
        $totalAmount = $totalSalesAmount + $totalPurchaseAmount;
        $totalPaid = $totalPaidAmount + $totalPurchasePaid;

        // Group by customer/supplier for summary
        $customerBreakdown = collect($dueReport)->groupBy('customer')
            ->map(function ($items, $customer) {
                return [
                    'name' => $customer,
                    'total_due' => $items->sum('due_amount'),
                    'count' => $items->count(),
                    'type' => $items->first()['type'],
                ];
            })->sortByDesc('total_due');

        return view('admin.due-reports.index', compact(
            'dueReport',
            'totalDue',
            'totalAmount',
            'totalPaid',
            'totalSalesDue',
            'totalPurchasesDue',
            'customerBreakdown'
        ));
    }
}
