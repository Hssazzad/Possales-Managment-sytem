<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnItem;
use Illuminate\Http\Request;

class ReturnsController extends Controller
{
    public function index()
    {
        $returns = ReturnItem::with(['sale', 'customer', 'product'])->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.returns.index', compact('returns'));
    }

    public function create()
    {
        return view('admin.returns.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'customer_id' => 'nullable|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
            'refund_amount' => 'required|numeric|min:0',
            'refund_method' => 'required|string|in:cash,card,bank,credit',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['return_date'] = now();
        $validated['status'] = 'pending';

        ReturnItem::create($validated);

        return redirect()->route('returns.index')
            ->with('success', 'Return created successfully');
    }

    public function show($id)
    {
        $return = ReturnItem::with(['sale', 'customer', 'product'])->findOrFail($id);
        return view('admin.returns.show', compact('return'));
    }

    public function edit($id)
    {
        $return = ReturnItem::findOrFail($id);
        return view('admin.returns.edit', compact('return'));
    }

    public function update(Request $request, $id)
    {
        $return = ReturnItem::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
            'refund_amount' => 'required|numeric|min:0',
            'refund_method' => 'required|string|in:cash,card,bank,credit',
            'status' => 'required|string|in:pending,approved,rejected,completed',
            'notes' => 'nullable|string|max:1000',
        ]);

        $return->update($validated);

        return redirect()->route('returns.index')
            ->with('success', 'Return updated successfully');
    }

    public function destroy($id)
    {
        $return = ReturnItem::findOrFail($id);
        $return->delete();

        return redirect()->route('returns.index')
            ->with('success', 'Return deleted successfully');
    }
}
