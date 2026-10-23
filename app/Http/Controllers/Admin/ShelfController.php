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
     * ✅ $racks যোগ করা হয়েছে — modal-এ দরকার
     */
    public function index()
    {
        $shelves = Shelf::with('rack')
                        ->withCount('products')
                        ->orderBy('name')
                        ->paginate(20);

        $racks = Rack::where('is_active', true)->orderBy('name')->get();

        return view('admin.shelves.index', compact('shelves', 'racks'));
    }

    /**
     * Show the form for creating a new resource.
     * (Legacy page — modal দিয়ে replace হয়েছে, তবুও রাখা হলো)
     */
    public function create()
    {
        $racks = Rack::where('is_active', true)->orderBy('name')->get();
        return view('admin.shelves.create', compact('racks'));
    }

    /**
     * Store a newly created resource in storage.
     * ✅ AJAX (modal) + normal redirect — দুটোই support করে
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50|unique:shelves',
            'rack_id'     => 'nullable|exists:racks,id',
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'nullable|boolean',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $shelf = Shelf::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Shelf created successfully!',
                'shelf'   => $shelf,
            ]);
        }

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
     * (Legacy page — modal দিয়ে replace হয়েছে, তবুও রাখা হলো)
     */
    public function edit(string $id)
    {
        $shelf = Shelf::findOrFail($id);
        $racks = Rack::where('is_active', true)->orderBy('name')->get();
        return view('admin.shelves.edit', compact('shelf', 'racks'));
    }

    /**
     * Update the specified resource in storage.
     * ✅ AJAX (modal) + normal redirect — দুটোই support করে
     */
    public function update(Request $request, string $id)
    {
        $shelf = Shelf::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50|unique:shelves,code,' . $id,
            'rack_id'     => 'nullable|exists:racks,id',
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'nullable|boolean',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $shelf->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Shelf updated successfully!',
                'shelf'   => $shelf->fresh(),
            ]);
        }

        return redirect()->route('shelves.index')
                         ->with('success', 'Shelf updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     * ✅ AJAX (fetch) + normal redirect — দুটোই support করে
     */
    public function destroy(string $id)
    {
        $shelf = Shelf::findOrFail($id);
        $shelf->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Shelf deleted successfully!',
            ]);
        }

        return redirect()->route('shelves.index')
                         ->with('success', 'Shelf deleted successfully!');
    }
}