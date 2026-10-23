<?php

namespace App\Http\Controllers;

use App\Models\ReturnItem;
use Illuminate\Http\Request;

class SaleReturnReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = ReturnItem::with(['sale', 'customer', 'product'])
            ->orderBy('created_at', 'desc');

        // Filter by date range if provided
        if ($request->filled('start_date')) {
            $query->whereDate('return_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('return_date', '<=', $request->end_date);
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $returnItems = $query->get();

        // Calculate totals
        $totalReturns = $returnItems->count();
        $totalRefundAmount = $returnItems->sum('refund_amount');
        $totalQuantity = $returnItems->sum('quantity');

        // Get status counts
        $pendingCount = $returnItems->where('status', 'pending')->count();
        $approvedCount = $returnItems->where('status', 'approved')->count();
        $completedCount = $returnItems->where('status', 'completed')->count();

        return view('admin.sale-return-reports.index', compact(
            'returnItems',
            'totalReturns',
            'totalRefundAmount',
            'totalQuantity',
            'pendingCount',
            'approvedCount',
            'completedCount'
        ));
    }
}
