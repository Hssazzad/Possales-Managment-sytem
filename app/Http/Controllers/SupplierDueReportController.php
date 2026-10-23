<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierDueReportController extends Controller
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

        $purchases = $query->get();

        // Calculate supplier due amounts
        $supplierDueReport = [];
        $totalSupplierDue = 0;
        $totalPurchaseAmount = 0;
        $totalPaidAmount = 0;

        foreach ($purchases as $purchase) {
            $dueAmount = $purchase->due_amount;

            if ($dueAmount > 0) {
                $supplierDueReport[] = [
                    'date' => $purchase->created_at->format('Y-m-d'),
                    'invoice' => $purchase->invoice_number,
                    'supplier' => $purchase->supplier->name ?? 'Unknown Supplier',
                    'supplier_id' => $purchase->supplier_id,
                    'total_amount' => $purchase->total_amount,
                    'paid_amount' => $purchase->paid_amount,
                    'due_amount' => $dueAmount,
                    'payment_status' => $purchase->payment_status,
                    'notes' => $purchase->notes ?? '',
                ];

                $totalSupplierDue += $dueAmount;
                $totalPurchaseAmount += $purchase->total_amount;
                $totalPaidAmount += $purchase->paid_amount;
            }
        }

        // Group by supplier for summary
        $supplierBreakdown = collect($supplierDueReport)->groupBy('supplier')
            ->map(function ($items, $supplier) {
                return [
                    'name' => $supplier,
                    'supplier_id' => $items->first()['supplier_id'],
                    'total_due' => $items->sum('due_amount'),
                    'total_amount' => $items->sum('total_amount'),
                    'paid_amount' => $items->sum('paid_amount'),
                    'count' => $items->count(),
                ];
            })->sortByDesc('total_due');

        // Get all suppliers for filter
        $suppliers = Supplier::active()->get();

        // Calculate additional metrics
        $supplierCount = $supplierBreakdown->count();
        $averageDuePerSupplier = $supplierCount > 0 ? $totalSupplierDue / $supplierCount : 0;

        return view('admin.supplier-due-reports.index', compact(
            'supplierDueReport',
            'totalSupplierDue',
            'totalPurchaseAmount',
            'totalPaidAmount',
            'supplierBreakdown',
            'suppliers',
            'supplierCount',
            'averageDuePerSupplier'
        ));
    }
}
