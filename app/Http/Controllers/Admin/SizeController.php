<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function index()
    {
        $sizes = Size::orderBy('name')->get();
        if (request()->ajax()) {
            return response()->json($sizes);
        }
        return view('admin.sizes.index', compact('sizes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sizes,name',
            'is_active' => 'required|in:0,1',
        ]);

        $size = Size::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Size created successfully!',
            'data' => $size
        ]);
    }

    public function edit($id)
    {
        $size = Size::findOrFail($id);
        return response()->json($size);
    }

    public function update(Request $request, $id)
    {
        $size = Size::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sizes,name,' . $id,
            'is_active' => 'required|in:0,1',
        ]);

        $size->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Size updated successfully!',
            'data' => $size
        ]);
    }

    public function destroy($id)
    {
        $size = Size::findOrFail($id);
        $size->delete();

        return response()->json([
            'success' => true,
            'message' => 'Size deleted successfully!'
        ]);
    }

    public function toggleStatus($id)
    {
        $size = Size::findOrFail($id);
        $size->is_active = $size->is_active == 1 ? 0 : 1;
        $size->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully!',
            'status' => $size->is_active
        ]);
    }
}
