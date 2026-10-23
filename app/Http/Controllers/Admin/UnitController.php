<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     * ✅ withCount('products') যোগ করা হয়েছে
     */
    public function index()
    {
        $units = Unit::withCount('products')
                     ->orderBy('name')
                     ->paginate(20);

        return view('admin.units.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     * (Legacy — modal দিয়ে replace হয়েছে)
     */
    public function create()
    {
        return view('admin.units.create');
    }

    /**
     * Store a newly created resource in storage.
     * ✅ slug সরানো, short_name যোগ, AJAX + redirect support
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:units',
            'short_name'  => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'nullable|boolean',
        ]);

        if (empty($validated['slug'] ?? '')) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $unit = Unit::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Unit created successfully!',
                'unit'    => $unit,
            ]);
        }

        return redirect()->route('units.index')
                         ->with('success', 'Unit created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $unit = Unit::findOrFail($id);
        return view('admin.units.show', compact('unit'));
    }

    /**
     * Show the form for editing the specified resource.
     * (Legacy — modal দিয়ে replace হয়েছে)
     */
    public function edit(string $id)
    {
        $unit = Unit::findOrFail($id);
        return view('admin.units.edit', compact('unit'));
    }

    /**
     * Update the specified resource in storage.
     * ✅ slug সরানো, short_name যোগ, AJAX + redirect support
     */
    public function update(Request $request, string $id)
    {
        $unit = Unit::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:units,slug,' . $id,
            'short_name'  => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'nullable|boolean',
        ]);

        if (empty($validated['slug'] ?? '')) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $unit->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Unit updated successfully!',
                'unit'    => $unit->fresh(),
            ]);
        }

        return redirect()->route('units.index')
                         ->with('success', 'Unit updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     * ✅ AJAX fetch + redirect support
     */
    public function destroy(string $id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Unit deleted successfully!',
            ]);
        }

        return redirect()->route('units.index')
                         ->with('success', 'Unit deleted successfully!');
    }
}