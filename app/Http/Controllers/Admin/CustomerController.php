<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'party_type' => 'nullable|string|max:100',
            'balance' => 'nullable|numeric|min:0',
            'due_amount' => 'nullable|numeric|min:0',
            'email' => 'nullable|email|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:500',
            'billing_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        Customer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'customer' => $validated
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->get('search');

        $customers = Customer::active()
            ->search($search)
            ->limit(10)
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'due_amount' => $customer->formatted_due_amount,
                    'display_text' => $customer->name . ' - ' . $customer->phone
                ];
            });

        return response()->json($customers);
    }

    public function index()
    {
        $customers = Customer::active()->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.customers.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully');
    }
}
