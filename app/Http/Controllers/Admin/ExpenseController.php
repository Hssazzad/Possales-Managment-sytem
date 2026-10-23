<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenses = Expense::with(['user', 'expenseCategory'])
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get category colors for display
        $categoryColors = ExpenseCategory::active()->pluck('color', 'name')->toArray();

        // Summary Cards Data
        $todayExpenses = Expense::whereDate('expense_date', today())->sum('amount');
        $monthlyExpenses = Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');
        $totalOperationsCost = Expense::sum('amount');

        return view('admin.expenses.index', compact(
            'expenses',
            'categoryColors',
            'todayExpenses',
            'monthlyExpenses',
            'totalOperationsCost'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = [
            'Office Supplies',
            'Utilities',
            'Rent',
            'Maintenance',
            'Marketing',
            'Transportation',
            'Food & Beverages',
            'Software & Tools',
            'Other',
        ];

        $paymentMethods = [
            'Cash',
            'Bank Transfer',
            'Credit Card',
            'Mobile Banking',
            'Check',
        ];

        return view('admin.expenses.create', compact('categories', 'paymentMethods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'expense_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
            'receipt_number' => 'nullable|string|max:50',
        ]);

        Expense::create([
            'description' => $request->description,
            'amount' => $request->amount,
            'category' => $request->category,
            'expense_date' => $request->expense_date,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
            'receipt_number' => $request->receipt_number,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $expense = Expense::with('user')->findOrFail($id);

        return view('admin.expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $expense = Expense::findOrFail($id);

        $categories = [
            'Office Supplies',
            'Utilities',
            'Rent',
            'Maintenance',
            'Marketing',
            'Transportation',
            'Food & Beverages',
            'Software & Tools',
            'Other',
        ];

        $paymentMethods = [
            'Cash',
            'Bank Transfer',
            'Credit Card',
            'Mobile Banking',
            'Check',
        ];

        return view('admin.expenses.edit', compact('expense', 'categories', 'paymentMethods'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'expense_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
            'receipt_number' => 'nullable|string|max:50',
        ]);

        $expense = Expense::findOrFail($id);
        $expense->update([
            'description' => $request->description,
            'amount' => $request->amount,
            'category' => $request->category,
            'expense_date' => $request->expense_date,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
            'receipt_number' => $request->receipt_number,
        ]);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully!');
    }

    public function categories()
    {
        // Get expense categories from database with real calculations
        $categories = ExpenseCategory::active()->get()->map(function ($category) {
            // Calculate real data based on expenses
            $totalExpense = 0;
            $transactionCount = 0;

            // Get expenses for this category
            $expensesData = DB::table('expenses')
                ->where('category', $category->name)
                ->selectRaw('SUM(amount) as total, COUNT(*) as count')
                ->first();

            $totalExpense = $expensesData->total ?? 0;
            $transactionCount = $expensesData->count ?? 0;

            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'total_expense' => $totalExpense,
                'transaction_count' => $transactionCount,
                'color' => $category->color,
            ];
        });

        return view('admin.expenses.categories', compact('categories'));
    }

    public function createCategory()
    {
        return view('admin.expenses.create-category');
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories',
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|max:7',
            'is_active' => 'boolean',
        ]);

        ExpenseCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('expenses.categories')
            ->with('success', 'Expense category created successfully!');
    }

    public function editCategory($id)
    {
        $category = ExpenseCategory::findOrFail($id);

        return view('admin.expenses.edit-category', compact('category'));
    }

    public function updateCategory(Request $request, $id)
    {
        $category = ExpenseCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,'.$id,
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|max:7',
            'is_active' => 'boolean',
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('expenses.categories')
            ->with('success', 'Expense category updated successfully!');
    }

    public function destroyCategory($id)
    {
        $category = ExpenseCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('expenses.categories')
            ->with('success', 'Expense category deleted successfully!');
    }
}
