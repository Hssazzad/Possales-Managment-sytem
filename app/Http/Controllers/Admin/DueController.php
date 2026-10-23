<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;

class DueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get sales with due amounts from sales table
        $salesWithDues = Sale::where('due_amount', '>', 0)
            ->with(['customer', 'user', 'items'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by customer name to avoid duplicates (handle null customer_id)
        $customerDues = $salesWithDues->groupBy(function ($sale) {
            // Use customer name as key to group properly, even if customer_id is null
            return $sale->customer_name ?: 'Walk-in Customer';
        })
            ->map(function ($customerSales) {
                $firstSale = $customerSales->first();
                $totalDue = $customerSales->sum('due_amount');
                $totalSales = $customerSales->count();

                return [
                    'customer_id' => $firstSale->customer_id,
                    'customer_name' => $firstSale->customer_name ?: 'Walk-in Customer',
                    'customer_phone' => $firstSale->customer_phone,
                    'total_due' => $totalDue,
                    'total_sales' => $totalSales,
                    'sales' => $customerSales,
                    'last_sale_date' => $firstSale->created_at,
                ];
            })
            ->sortByDesc('last_sale_date');

        return view('admin.dues.index', compact('customerDues', 'salesWithDues'));
    }

    /**
     * Display customer dues for POS access
     */
    public function customerIndex()
    {
        // Same logic as index() but return a different view for POS
        $salesWithDues = Sale::where('due_amount', '>', 0)
            ->with(['customer', 'user', 'items'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by customer to show total due per customer
        $customerDues = $salesWithDues->groupBy(function ($sale) {
            // Use customer name as key to group properly, even if customer_id is null
            return $sale->customer_name ?: 'Walk-in Customer';
        })
            ->map(function ($customerSales) {
                $firstSale = $customerSales->first();
                $totalDue = $customerSales->sum('due_amount');
                $totalSales = $customerSales->count();

                return [
                    'customer_id' => $firstSale->customer_id,
                    'customer_name' => $firstSale->customer_name ?: 'Walk-in Customer',
                    'customer_phone' => $firstSale->customer_phone,
                    'total_due' => $totalDue,
                    'total_sales' => $totalSales,
                    'sales' => $customerSales,
                    'last_sale_date' => $firstSale->created_at,
                ];
            })
            ->sortByDesc('last_sale_date');

        return view('admin.dues.customer-index', compact('customerDues', 'salesWithDues'));
    }

    /**
     * Display supplier dues for POS access
     */
    public function supplierIndex()
    {
        // Get purchases with due amounts from purchases table
        $purchasesWithDues = Purchase::where('due_amount', '>', 0)
            ->with(['supplier', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by supplier to show total due per supplier
        $supplierDues = $purchasesWithDues->groupBy(function ($purchase) {
            // Use supplier name as key to group properly, even if supplier_id is null
            return $purchase->supplier ? $purchase->supplier->name : 'Unknown Supplier';
        })
            ->map(function ($supplierPurchases) {
                $firstPurchase = $supplierPurchases->first();
                $totalDue = $supplierPurchases->sum('due_amount');
                $totalPurchases = $supplierPurchases->count();

                return [
                    'supplier_id' => $firstPurchase->supplier_id,
                    'supplier_name' => $firstPurchase->supplier ? $firstPurchase->supplier->name : 'Unknown Supplier',
                    'supplier_company' => $firstPurchase->supplier ? $firstPurchase->supplier->company_name : null,
                    'supplier_phone' => $firstPurchase->supplier ? $firstPurchase->supplier->phone : null,
                    'total_due' => $totalDue,
                    'total_purchases' => $totalPurchases,
                    'purchases' => $supplierPurchases,
                    'last_purchase_date' => $firstPurchase->created_at,
                ];
            })
            ->sortByDesc('last_purchase_date');

        return view('admin.dues.supplier-index', compact('supplierDues', 'purchasesWithDues'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.dues.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'type' => 'required|in:pending,paid,overdue,cancelled',
            'party_name' => 'nullable|string|max:255',
            'party_type' => 'required|in:supplier,customer,employee',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'payment_method' => 'nullable|string|max:50',
            'paid_amount' => 'nullable|numeric|min:0',
            'paid_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        Due::create([
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'due_date' => $request->due_date,
            'type' => $request->type,
            'party_name' => $request->party_name,
            'party_type' => $request->party_type,
            'contact_person' => $request->contact_person,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'payment_method' => $request->payment_method,
            'paid_amount' => $request->paid_amount,
            'paid_date' => $request->paid_date,
            'notes' => $request->notes,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('dues.index')
            ->with('success', 'Due added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $due = Due::with('user')->findOrFail($id);

        return view('admin.dues.show', compact('due'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $due = Due::findOrFail($id);

        return view('admin.dues.edit', compact('due'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'type' => 'required|in:pending,paid,overdue,cancelled',
            'party_name' => 'nullable|string|max:255',
            'party_type' => 'required|in:supplier,customer,employee',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'payment_method' => 'nullable|string|max:50',
            'paid_amount' => 'nullable|numeric|min:0',
            'paid_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $due = Due::findOrFail($id);
        $due->update([
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'due_date' => $request->due_date,
            'type' => $request->type,
            'party_name' => $request->party_name,
            'party_type' => $request->party_type,
            'contact_person' => $request->contact_person,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'payment_method' => $request->payment_method,
            'paid_amount' => $request->paid_amount,
            'paid_date' => $request->paid_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('dues.index')
            ->with('success', 'Due updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $due = Due::findOrFail($id);
        $due->delete();

        return redirect()->route('dues.index')
            ->with('success', 'Due deleted successfully!');
    }
}
