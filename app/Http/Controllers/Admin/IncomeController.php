<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\IncomeCategory;

class IncomeController extends Controller
{
    public function index()
    {
        // Get sales category from database
        $salesCategory = IncomeCategory::where('name', 'Sales')->where('is_active', true)->first();
        $categoryName = $salesCategory ? $salesCategory->name : 'Sales';

        // Get income data from sales table - grouped by date
        $dailyIncomes = DB::table('sales')
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as amount, COUNT(*) as transaction_count')
            ->where('payment_status', 'paid')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($item, $index) use ($categoryName) {
                return [
                    'id' => $index + 1,
                    'date' => $item->date,
                    'description' => 'Daily Sales (' . $item->transaction_count . ' transactions)',
                    'amount' => $item->amount,
                    'type' => $categoryName
                ];
            });

        // You can also add other income sources here in the future
        // For example: services, fees, etc.
        return view('admin.income.index', compact('dailyIncomes'));
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

        IncomeCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'is_active' => $request->has('is_active') ? true : false
        ]);

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
