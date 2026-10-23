<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vat;
use Illuminate\Http\Request;

class VatController extends Controller
{
    /**
     * Display a listing of VAT rates.
     */
    public function index()
    {
        $vats = Vat::active()->get();

        return view('admin.vats.index', compact('vats'));
    }

    /**
     * Show the form for creating a new VAT rate.
     */
    public function create()
    {
        return view('admin.vats.create');
    }

    /**
     * Store a newly created VAT rate in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:vats',
            'rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        Vat::create([
            'name' => $request->name,
            'rate' => $request->rate,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('vats.index')
            ->with('success', 'VAT rate created successfully!');
    }

    /**
     * Display the specified VAT rate.
     */
    public function show($id)
    {
        $vat = Vat::findOrFail($id);

        return view('admin.vats.show', compact('vat'));
    }

    /**
     * Show the form for editing the specified VAT rate.
     */
    public function edit($id)
    {
        $vat = Vat::findOrFail($id);

        return view('admin.vats.edit', compact('vat'));
    }

    /**
     * Update the specified VAT rate in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:vats,name,'.$id,
            'rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $vat = Vat::findOrFail($id);
        $vat->update([
            'name' => $request->name,
            'rate' => $request->rate,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('vats.index')
            ->with('success', 'VAT rate updated successfully!');
    }

    /**
     * Remove the specified VAT rate from storage.
     */
    public function destroy($id)
    {
        $vat = Vat::findOrFail($id);
        $vat->delete();

        return redirect()->route('vats.index')
            ->with('success', 'VAT rate deleted successfully!');
    }
}
