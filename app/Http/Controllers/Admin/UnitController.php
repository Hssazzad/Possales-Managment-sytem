<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::orderBy('name')->paginate(20);
        return view('admin.units.index', compact('units'));
    }

    public function create()
    {
        return view('admin.units.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:units',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        Unit::create($validated);

        return redirect()->route('units.index')
            ->with('success', 'Unit created successfully!');
    }

    public function show(string $id)
    {
        $unit = Unit::findOrFail($id);
        return view('admin.units.show', compact('unit'));
    }

    public function edit(string $id)
    {
        $unit = Unit::findOrFail($id);
        return view('admin.units.edit', compact('unit'));
    }

    public function update(Request $request, string $id)
    {
        $unit = Unit::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:units,slug,' . $id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $unit->update($validated);

        return redirect()->route('units.index')
            ->with('success', 'Unit updated successfully!');
    }

    public function destroy(string $id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return redirect()->route('units.index')
            ->with('success', 'Unit deleted successfully!');
    }
}
