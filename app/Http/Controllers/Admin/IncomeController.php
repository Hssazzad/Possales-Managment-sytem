<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\Due;
use App\Models\IncomeCategory;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $dateFilter = $request->get('date', today()->toDateString());

        // 1. Product Sales Income - from sales table (paid_amount > 0)
        $salesIncome = Sale::whereDate('created_at', $dateFilter)
            ->where('paid_amount', '>', 0)
            ->select('id', 'invoice_number', 'paid_amount', 'payment_method', 'customer_name', 'created_at')
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => 'SALE-' . $sale->id,
                    'date' => $sale->created_at->format('Y-m-d H:i:s'),
                    'source' => 'Product Sales Income',
                    'reference' => $sale->invoice_number . ($sale->customer_name ? ' - ' . $sale->customer_name : ''),
                    'amount' => $sale->paid_amount,
                    'payment_method' => $sale->payment_method,
                    'type' => 'sales',
                    'sale_id' => $sale->id
                ];
            });

        // 2. Due Collection - from dues table (paid dues from customers)
        $dueCollections = Due::whereDate('paid_date', $dateFilter)
            ->where('party_type', 'customer')
            ->where('type', 'paid')
            ->where('paid_amount', '>', 0)
            ->select('id', 'title', 'party_name', 'paid_amount', 'payment_method', 'paid_date')
            ->get()
            ->map(function ($due) {
                return [
                    'id' => 'DUE-' . $due->id,
                    'date' => $due->paid_date ? $due->paid_date->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                    'source' => 'Due Collection',
                    'reference' => $due->party_name . ' - ' . $due->title,
                    'amount' => $due->paid_amount,
                    'payment_method' => $due->payment_method ?? 'Cash',
                    'type' => 'due',
                    'due_id' => $due->id
                ];
            });

        // Combine both income sources
        $allIncomes = $salesIncome->concat($dueCollections)->sortByDesc('date')->values();

        // Summary Cards Data
        $todaySalesIncome = Sale::whereDate('created_at', today())->sum('paid_amount');
        $todayDueCollected = Due::whereDate('paid_date', today())
            ->where('party_type', 'customer')
            ->where('type', 'paid')
            ->sum('paid_amount');
        $totalCashInHand = $todaySalesIncome + $todayDueCollected;

        // Date filter totals
        $filteredSalesIncome = $salesIncome->sum('amount');
        $filteredDueCollected = $dueCollections->sum('amount');
        $filteredTotal = $filteredSalesIncome + $filteredDueCollected;

        return view('admin.income.index', compact(
            'allIncomes',
            'todaySalesIncome',
            'todayDueCollected',
            'totalCashInHand',
            'filteredSalesIncome',
            'filteredDueCollected',
            'filteredTotal',
            'dateFilter'
        ));
    }

    public function categories()
    {
        // Get income categories from database with real calculations
        $categories = IncomeCategory::active()->get()->map(function ($category) {
            $totalIncome = 0;
            $transactionCount = 0;

            // Calculate real data based on category type
            switch ($category->name) {
                case 'Sales':
                    $salesData = DB::table('sales')
                        ->where('payment_status', 'paid')
                        ->selectRaw('SUM(total_amount) as total, COUNT(*) as count')
                        ->first();
                    $totalIncome = $salesData->total ?? 0;
                    $transactionCount = $salesData->count ?? 0;
                    break;

                case 'Services':
                    // You can add services table logic here when available
                    $totalIncome = 0;
                    $transactionCount = 0;
                    break;

                case 'Other':
                    // You can add other income sources logic here when available
                    $totalIncome = 0;
                    $transactionCount = 0;
                    break;

                default:
                    // For any custom categories, default to 0 until data is available
                    $totalIncome = 0;
                    $transactionCount = 0;
                    break;
            }

            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'total_income' => $totalIncome,
                'transaction_count' => $transactionCount,
                'color' => $category->color
            ];
        });

        return view('admin.income.categories', compact('categories'));
    }

    public function create()
    {
        return view('admin.income.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:income_categories',
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|max:7',
            'is_active' => 'boolean'
        ]);

        $category = IncomeCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'is_active' => $request->has('is_active') ? true : false
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Income category created successfully!',
                'category' => $category
            ]);
        }

        return redirect()->route('income.categories')
            ->with('success', 'Income category created successfully!');
    }

    public function edit($id)
    {
        $category = IncomeCategory::findOrFail($id);
        return view('admin.income.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = IncomeCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:income_categories,name,' . $id,
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|max:7',
            'is_active' => 'boolean'
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'is_active' => $request->has('is_active') ? true : false
        ]);

        return redirect()->route('income.categories')
            ->with('success', 'Income category updated successfully!');
    }

    public function destroy($id)
    {
        $category = IncomeCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('income.categories')
            ->with('success', 'Income category deleted successfully!');
    }
}
