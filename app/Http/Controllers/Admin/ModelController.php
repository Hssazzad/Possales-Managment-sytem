<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Model;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ModelController extends Controller
{
    public function index()
    {
        $models = Model::with('brand')->orderBy('name')->paginate(20);
        return view('admin.models.index', compact('models'));
    }

    public function create()
    {
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        return view('admin.models.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:models',
            'brand_id' => 'nullable|exists:brands,id',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/models', $imageName);
            $validated['image'] = 'models/' . $imageName;
        }

        Model::create($validated);

        return redirect()->route('models.index')
            ->with('success', 'Model created successfully!');
    }

    public function show(string $id)
    {
        $model = Model::with('brand')->findOrFail($id);
        return view('admin.models.show', compact('model'));
    }

    public function edit(string $id)
    {
        $model = Model::findOrFail($id);
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        return view('admin.models.edit', compact('model', 'brands'));
    }

    public function update(Request $request, string $id)
    {
        $model = Model::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:models,slug,' . $id,
            'brand_id' => 'nullable|exists:brands,id',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            if ($model->image && Storage::exists('public/' . $model->image)) {
                Storage::delete('public/' . $model->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/models', $imageName);
            $validated['image'] = 'models/' . $imageName;
        }

        $model->update($validated);

        return redirect()->route('models.index')
            ->with('success', 'Model updated successfully!');
    }

    public function destroy(string $id)
    {
        $model = Model::findOrFail($id);

        if ($model->image && Storage::exists('public/' . $model->image)) {
            Storage::delete('public/' . $model->image);
        }

        $model->delete();

        return redirect()->route('models.index')
            ->with('success', 'Model deleted successfully!');
    }
}
