<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('brand')->orderBy('name')->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $variationTypes = ['Capacity', 'Color', 'Size', 'Type', 'Weight'];
        return view('admin.categories.create', compact('brands', 'variationTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories',
            'icon' => 'nullable|string|max:100',
            'brand_id' => 'nullable|exists:brands,id',
            'variations' => 'nullable|array',
            'variations.*' => 'string|in:Capacity,Color,Size,Type,Weight',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully!');
    }

    public function show(string $id)
    {
        $category = Category::with('brand')->findOrFail($id);
        return view('admin.categories.show', compact('category'));
    }

    public function edit(string $id)
    {
        $category = Category::findOrFail($id);
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $variationTypes = ['Capacity', 'Color', 'Size', 'Type', 'Weight'];
        return view('admin.categories.edit', compact('category', 'brands', 'variationTypes'));
    }

    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $id,
            'icon' => 'nullable|string|max:100',
            'brand_id' => 'nullable|exists:brands,id',
            'variations' => 'nullable|array',
            'variations.*' => 'string|in:Capacity,Color,Size,Type,Weight',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully!');
    }

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully!');
    }
}
