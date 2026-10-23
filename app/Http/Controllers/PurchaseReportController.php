<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Purchase::with(['supplier'])
            ->orderBy('created_at', 'desc');

        // Filter by date range if provided
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by supplier if provided
        if ($request->filled('supplier')) {
            $query->where('supplier_id', $request->supplier);
        }

        // Filter by payment status if provided
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $purchases = $query->get();

        // Calculate totals
        $totalPurchases = $purchases->sum('total_amount');
        $totalPaid = $purchases->sum('paid_amount');
        $totalDue = $totalPurchases - $totalPaid;
        $purchaseCount = $purchases->count();

        // Group by supplier for summary
        $supplierBreakdown = $purchases->groupBy('supplier_id')
            ->map(function ($purchases, $supplierId) {
                $supplier = $purchases->first()->supplier;

                return [
                    'supplier_name' => $supplier ? $supplier->name : 'Unknown Supplier',
                    'supplier_id' => $supplierId,
                    'total_amount' => $purchases->sum('total_amount'),
                    'paid_amount' => $purchases->sum('paid_amount'),
                    'due_amount' => $purchases->sum('total_amount') - $purchases->sum('paid_amount'),
                    'count' => $purchases->count(),
                ];
            })->sortByDesc('total_amount');

        // Monthly breakdown
        $monthlyPurchases = [];
        foreach ($purchases as $purchase) {
            $monthYear = $purchase->created_at->format('Y-m');
            if (! isset($monthlyPurchases[$monthYear])) {
                $monthlyPurchases[$monthYear] = [
                    'month' => $purchase->created_at->format('F Y'),
                    'total' => 0,
                    'count' => 0,
                ];
            }
            $monthlyPurchases[$monthYear]['total'] += $purchase->total_amount;
            $monthlyPurchases[$monthYear]['count']++;
        }
        ksort($monthlyPurchases);
        $monthlyPurchases = array_values($monthlyPurchases);

        // Payment method breakdown
        $paymentMethodBreakdown = $purchases->groupBy('payment_method')
            ->map(function ($purchases, $method) {
                return [
                    'method' => $method ?: 'Other',
                    'total' => $purchases->sum('total_amount'),
                    'count' => $purchases->count(),
                ];
            })->sortByDesc('total');

        // Get all suppliers for filter
        $suppliers = Supplier::active()->get();

        return view('admin.purchase-reports.index', compact(
            'purchases',
            'totalPurchases',
            'totalPaid',
            'totalDue',
            'purchaseCount',
            'supplierBreakdown',
            'monthlyPurchases',
            'paymentMethodBreakdown',
            'suppliers'
        ));
    }
}
