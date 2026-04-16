<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shelf;
use App\Models\Rack;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShelfController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shelves = Shelf::with('rack')->orderBy('name')->paginate(20);
        return view('admin.shelves.index', compact('shelves'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $racks = Rack::where('is_active', true)->orderBy('name')->get();
        return view('admin.shelves.create', compact('racks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:shelves',
            'rack_id' => 'nullable|exists:racks,id',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        Shelf::create($validated);

        return redirect()->route('shelves.index')
            ->with('success', 'Shelf created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $shelf = Shelf::with('rack')->findOrFail($id);
        return view('admin.shelves.show', compact('shelf'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $shelf = Shelf::findOrFail($id);
        $racks = Rack::where('is_active', true)->orderBy('name')->get();
        return view('admin.shelves.edit', compact('shelf', 'racks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $shelf = Shelf::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:shelves,code,' . $id,
            'rack_id' => 'nullable|exists:racks,id',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $shelf->update($validated);

        return redirect()->route('shelves.index')
            ->with('success', 'Shelf updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $shelf = Shelf::findOrFail($id);
        $shelf->delete();

        return redirect()->route('shelves.index')
            ->with('success', 'Shelf deleted successfully!');
    }
}
