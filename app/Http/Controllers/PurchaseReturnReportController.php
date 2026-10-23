<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseReturnReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Since there might not be a dedicated PurchaseReturn model,
        // we'll work with Purchase model and filter for returns
        // This assumes purchase returns are marked with a specific status or type

        $query = Purchase::with(['supplier', 'user'])
            ->orderBy('created_at', 'desc');

        // Filter by date range if provided
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter for returns - this might need adjustment based on actual database structure
        // Assuming returns have a specific payment_status or a return flag
        $query->where(function ($q) {
            $q->where('payment_status', 'returned')
                ->orWhere('payment_status', 'refund')
                ->orWhere('notes', 'like', '%return%')
                ->orWhere('notes', 'like', '%refund%');
        });

        $purchaseReturns = $query->get();

        // Calculate totals
        $totalReturns = $purchaseReturns->count();
        $totalReturnAmount = $purchaseReturns->sum('total_amount');
        $totalRefundAmount = $purchaseReturns->sum('paid_amount');

        // Group by supplier for summary
        $supplierReturns = $purchaseReturns->groupBy('supplier_id')
            ->map(function ($returns) {
                return [
                    'supplier_name' => $returns->first()->supplier->name ?? 'Unknown',
                    'count' => $returns->count(),
                    'total_amount' => $returns->sum('total_amount'),
                    'refund_amount' => $returns->sum('paid_amount'),
                ];
            })->sortBy('supplier_name');

        return view('admin.purchase-return-reports.index', compact(
            'purchaseReturns',
            'totalReturns',
            'totalReturnAmount',
            'totalRefundAmount',
            'supplierReturns'
        ));
    }
}
