<?php

namespace App\Http\Controllers;

use App\Models\PaymentType;
use Illuminate\Http\Request;

class PaymentTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $paymentTypes = PaymentType::orderBy('name')->get();

        return view('admin.payment-types.index', compact('paymentTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:payment_types,name',
        ]);

        PaymentType::create([
            'name' => $request->name,
            'active' => true,
        ]);

        return redirect()->route('payment-types.index')
            ->with('success', 'Payment type added successfully!');
    }

    public function update(Request $request, PaymentType $paymentType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:payment_types,name,' . $paymentType->id,
        ]);

        $paymentType->update([
            'name' => $request->name,
        ]);

        return redirect()->route('payment-types.index')
            ->with('success', 'Payment type updated successfully!');
    }

    public function updateStatus(PaymentType $paymentType)
    {
        $paymentType->update([
            'active' => ! $paymentType->active,
        ]);

        return redirect()->route('payment-types.index')
            ->with('success', 'Payment type status updated successfully!');
    }

    public function destroy(PaymentType $paymentType)
    {
        $paymentType->delete();

        return redirect()->route('payment-types.index')
            ->with('success', 'Payment type deleted successfully!');
    }
}
