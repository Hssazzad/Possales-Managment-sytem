<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with('supplier')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.purchases.index', compact('purchases'));
    }

    public function create()
    {
        return view('admin.purchases.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'invoice_number' => 'nullable|string|max:255',
            'payment_method' => 'required|string|in:cash,card,bank,credit',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['payment_status'] = 'paid';

        $purchase = Purchase::create($validated);

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase saved successfully!');
    }

    public function show($id)
    {
        return view('admin.purchases.show', compact('id'));
    }

    public function edit($id)
    {
        return view('admin.purchases.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        // Purchase update logic here
        return redirect()->route('purchases.index')
            ->with('success', 'Purchase updated successfully');
    }

    public function destroy($id)
    {
        // Purchase deletion logic here
        return redirect()->route('purchases.index')
            ->with('success', 'Purchase deleted successfully');
    }
}
