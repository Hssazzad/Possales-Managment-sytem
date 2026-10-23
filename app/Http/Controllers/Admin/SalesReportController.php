<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ProductVariation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    /**
     * Display sales report with filters
     */
    public function index(Request $request)
    {
        // Date range filters
        $dateRange = $request->get('date_range', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Calculate date range
        $dates = $this->getDateRange($dateRange, $startDate, $endDate);
        $from = $dates['from'];
        $to = $dates['to'];

        // Build query
        $query = Sale::with(['customer', 'items.product', 'items.variation'])
            ->whereBetween('sale_date', [$from, $to]);

        // Apply additional filters
        if ($request->has('payment_status') && $request->payment_status !== '') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('customer_id') && $request->customer_id !== '') {
            $query->where('customer_id', $request->customer_id);
        }

        // Get sales with pagination
        $sales = $query->orderBy('sale_date', 'desc')
            ->paginate(50)
            ->appends($request->all());

        // Calculate summary
        $summary = $this->getSummary($from, $to, $request);

        // Get customers for filter dropdown
        $customers = \App\Models\Customer::select('id', 'name', 'phone')
            ->orderBy('name')
            ->get();

        return view('admin.reports.sales', compact(
            'sales',
            'summary',
            'customers',
            'dateRange',
            'startDate',
            'endDate',
            'from',
            'to'
        ));
    }

    /**
     * Calculate date range based on filter
     */
    private function getDateRange($range, $startDate, $endDate)
    {
        $today = Carbon::today();

        switch ($range) {
            case 'today':
                return [
                    'from' => $today->copy()->startOfDay(),
                    'to' => $today->copy()->endOfDay()
                ];

            case 'yesterday':
                return [
                    'from' => $today->copy()->subDay()->startOfDay(),
                    'to' => $today->copy()->subDay()->endOfDay()
                ];

            case 'last_7_days':
                return [
                    'from' => $today->copy()->subDays(6)->startOfDay(),
                    'to' => $today->copy()->endOfDay()
                ];

            case 'last_30_days':
                return [
                    'from' => $today->copy()->subDays(29)->startOfDay(),
                    'to' => $today->copy()->endOfDay()
                ];

            case 'this_month':
                return [
                    'from' => $today->copy()->startOfMonth()->startOfDay(),
                    'to' => $today->copy()->endOfMonth()->endOfDay()
                ];

            case 'last_month':
                return [
                    'from' => $today->copy()->subMonth()->startOfMonth()->startOfDay(),
                    'to' => $today->copy()->subMonth()->endOfMonth()->endOfDay()
                ];

            case 'custom':
                return [
                    'from' => $startDate ? Carbon::parse($startDate)->startOfDay() : $today->startOfDay(),
                    'to' => $endDate ? Carbon::parse($endDate)->endOfDay() : $today->endOfDay()
                ];

            default:
                return [
                    'from' => $today->copy()->startOfDay(),
                    'to' => $today->copy()->endOfDay()
                ];
        }
    }

    /**
     * Get summary statistics
     */
    private function getSummary($from, $to, Request $request)
    {
        $query = Sale::whereBetween('sale_date', [$from, $to]);

        // Apply same filters as main query
        if ($request->has('payment_status') && $request->payment_status !== '') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('customer_id') && $request->customer_id !== '') {
            $query->where('customer_id', $request->customer_id);
        }

        return [
            'total_sales' => $query->count(),
            'total_amount' => $query->sum('total_amount'),
            'total_discount' => $query->sum('discount_amount'),
            'total_paid' => $query->sum('paid_amount'),
            'total_due' => $query->sum('due_amount'),
            'total_tax' => $query->sum('tax_amount'),
        ];
    }

    /**
     * Export report to PDF
     */
    public function exportPdf(Request $request)
    {
        $dateRange = $request->get('date_range', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $dates = $this->getDateRange($dateRange, $startDate, $endDate);
        $from = $dates['from'];
        $to = $dates['to'];

        $query = Sale::with(['customer', 'items.product', 'items.variation'])
            ->whereBetween('sale_date', [$from, $to]);

        if ($request->has('payment_status') && $request->payment_status !== '') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('customer_id') && $request->customer_id !== '') {
            $query->where('customer_id', $request->customer_id);
        }

        $sales = $query->orderBy('sale_date', 'desc')->get();
        $summary = $this->getSummary($from, $to, $request);

        // Generate PDF using TCPDF or DomPDF
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.reports.sales_pdf', compact('sales', 'summary', 'from', 'to'));

        $filename = 'sales_report_' . $from->format('Y-m-d') . '_to_' . $to->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export report to Excel
     */
    public function exportExcel(Request $request)
    {
        $dateRange = $request->get('date_range', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $dates = $this->getDateRange($dateRange, $startDate, $endDate);
        $from = $dates['from'];
        $to = $dates['to'];

        $query = Sale::with(['customer', 'items.product', 'items.variation'])
            ->whereBetween('sale_date', [$from, $to]);

        if ($request->has('payment_status') && $request->payment_status !== '') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('customer_id') && $request->customer_id !== '') {
            $query->where('customer_id', $request->customer_id);
        }

        $sales = $query->orderBy('sale_date', 'desc')->get();

        // Generate Excel using Simple CSV for now
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales_report_' . $from->format('Y-m-d') . '_to_' . $to->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($sales) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'Invoice Number',
                'Date',
                'Customer Name',
                'Customer Phone',
                'Product Name',
                'Variation',
                'Quantity',
                'Unit Price',
                'Total',
                'Discount',
                'Tax',
                'Grand Total',
                'Paid Amount',
                'Due Amount',
                'Payment Status',
                'Payment Method'
            ]);

            // Data
            foreach ($sales as $sale) {
                foreach ($sale->items as $item) {
                    fputcsv($file, [
                        $sale->invoice_number,
                        $sale->sale_date->format('Y-m-d H:i'),
                        $sale->customer_name,
                        $sale->customer_phone,
                        $item->product_name,
                        $item->variation ? $item->variation->variation_value : '-',
                        $item->quantity,
                        $item->unit_price,
                        $item->total_price,
                        $sale->discount_amount,
                        $sale->tax_amount,
                        $sale->total_amount,
                        $sale->paid_amount,
                        $sale->due_amount,
                        $sale->payment_status,
                        $sale->payment_method
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
