<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function index()
    {
        $colors = Color::orderBy('name')->get();
        if (request()->ajax()) {
            return response()->json($colors);
        }
        return view('admin.colors.index', compact('colors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color_code' => 'required|string|max:7',
            'is_active' => 'required|in:0,1',
        ]);

        $color = Color::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Color created successfully!',
            'data' => $color
        ]);
    }

    public function edit($id)
    {
        $color = Color::findOrFail($id);
        return response()->json($color);
    }

    public function update(Request $request, $id)
    {
        $color = Color::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color_code' => 'required|string|max:7',
            'is_active' => 'required|in:0,1',
        ]);

        $color->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Color updated successfully!',
            'data' => $color
        ]);
    }

    public function destroy($id)
    {
        $color = Color::findOrFail($id);
        $color->delete();

        return response()->json([
            'success' => true,
            'message' => 'Color deleted successfully!'
        ]);
    }
}
