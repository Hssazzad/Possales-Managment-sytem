<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rack;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RackController extends Controller
{
    public function index()
    {
        $racks = Rack::orderBy('name')->paginate(20);
        return view('admin.racks.index', compact('racks'));
    }

    public function create()
    {
        return view('admin.racks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:racks',
            'description' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        Rack::create($validated);

        return redirect()->route('racks.index')
            ->with('success', 'Rack created successfully!');
    }

    public function show(string $id)
    {
        $rack = Rack::findOrFail($id);
        return view('admin.racks.show', compact('rack'));
    }

    public function edit(string $id)
    {
        $rack = Rack::findOrFail($id);
        return view('admin.racks.edit', compact('rack'));
    }

    public function update(Request $request, string $id)
    {
        $rack = Rack::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:racks,code,' . $id,
            'description' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $rack->update($validated);

        return redirect()->route('racks.index')
            ->with('success', 'Rack updated successfully!');
    }

    public function destroy(string $id)
    {
        $rack = Rack::findOrFail($id);
        $rack->delete();

        return redirect()->route('racks.index')
            ->with('success', 'Rack deleted successfully!');
    }
}
