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
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        return view('admin.categories.index', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'slug'         => 'nullable|string|max:255|unique:categories',
            'icon'         => 'nullable|string|max:100',
            'brand_id'     => 'nullable|exists:brands,id',
            'variations'   => 'nullable|array',
            'variations.*' => 'string|in:Capacity,Color,Size,Type,Weight',
            'description'  => 'nullable|string|max:1000',
            'is_active'    => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        Category::create($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully!',
            ]);
        }

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully!');
    }

    public function show(string $id)
    {
        $category = Category::with('brand')->findOrFail($id);
        return view('admin.categories.show', compact('category'));
    }

    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'slug'         => 'nullable|string|max:255|unique:categories,slug,' . $id,
            'icon'         => 'nullable|string|max:100',
            'brand_id'     => 'nullable|exists:brands,id',
            'variations'   => 'nullable|array',
            'variations.*' => 'string|in:Capacity,Color,Size,Type,Weight',
            'description'  => 'nullable|string|max:1000',
            'is_active'    => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $category->update($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully!',
            ]);
        }

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully!');
    }

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully!',
            ]);
        }

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully!');
    }

    // ✅ Product create form এ category select করলে variations আনার জন্য
    public function getVariations($id)
    {
        $category = Category::find($id);
        return response()->json([
            'variations' => $category ? ($category->variations ?? []) : []
        ]);
    }
}