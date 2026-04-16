<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:suppliers,phone',
            'email' => 'nullable|email|max:255',
            'company_name' => 'nullable|string|max:255',
            'supplier_type' => 'nullable|string|max:100',
            'balance' => 'nullable|numeric|min:0',
            'due_amount' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:500',
            'billing_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        Supplier::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Supplier created successfully',
            'supplier' => $validated
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->get('search');
        
        $suppliers = Supplier::active()
            ->search($search)
            ->limit(10)
            ->get()
            ->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'phone' => $supplier->phone,
                    'email' => $supplier->email,
                    'company_name' => $supplier->company_name,
                    'due_amount' => $supplier->formatted_due_amount,
                    'display_text' => $supplier->company_name ? $supplier->company_name . ' - ' . $supplier->name : $supplier->name
                ];
            });

        return response()->json($suppliers);
    }

    public function index()
    {
        $suppliers = Supplier::active()->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function show(Supplier $supplier)
    {
        return view('admin.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:suppliers,phone,' . $supplier->id,
            'email' => 'nullable|email|max:255',
            'company_name' => 'nullable|string|max:255',
            'supplier_type' => 'nullable|string|max:100',
            'balance' => 'nullable|numeric|min:0',
            'due_amount' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:500',
            'billing_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully');
    }
}
