<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Model as ProductModel;
use App\Models\Product;
use App\Models\Rack;
use App\Models\Shelf;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();
        $racks = Rack::where('is_active', true)->orderBy('name')->get();
        $shelves = Shelf::where('is_active', true)->with('rack')->orderBy('name')->get();
        $models = ProductModel::where('is_active', true)->with('brand')->orderBy('name')->get();

        return view('admin.products.create', compact('brands', 'categories', 'units', 'racks', 'shelves', 'models'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:products',
            'category' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'rack' => 'nullable|string|max:50',
            'shelf' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:50',
            'pricing_type' => 'nullable|string|in:single,batch',
            'purchase_price' => 'required|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'sale_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'barcode' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'manufacturing_date' => 'nullable|date',
            'expire_date' => 'nullable|date|after_or_equal:manufacturing_date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // Map new fields to legacy columns to avoid DB errors
        $validated['price'] = $validated['sale_price'];
        $validated['cost']  = $validated['purchase_price'];


        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/products', $imageName);
            $validated['image'] = 'products/' . $imageName;
        }

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully!');
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();
        $racks = Rack::where('is_active', true)->orderBy('name')->get();
        $shelves = Shelf::where('is_active', true)->with('rack')->orderBy('name')->get();
        $models = \App\Models\Model::where('is_active', true)->with('brand')->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'brands', 'categories', 'units', 'racks', 'shelves', 'models'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:products,code,' . $id,
            'category' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'barcode' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // Map new fields to legacy columns to avoid DB errors
        $validated['price'] = $validated['sale_price'];
        $validated['cost']  = $validated['purchase_price'];
        $validated['min_stock'] = $validated['min_stock_level'] ?? 0;

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && Storage::exists('public/' . $product->image)) {
                Storage::delete('public/' . $product->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/products', $imageName);
            $validated['image'] = 'products/' . $imageName;
        }

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully!');
    }

    public function search(Request $request)
    {
        $search = $request->get('search');

        $products = Product::where('is_active', true)
            ->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'sale_price' => $product->sale_price,
                    'stock_quantity' => $product->stock_quantity,
                ];
            });

        return response()->json($products);
    }

    /**
     * Show bulk upload form
     */
    public function bulkUpload()
    {
        return view('admin.products.bulk-upload');
    }

    /**
     * Download sample CSV file
     */
    public function downloadSample()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_sample.csv"',
        ];

        $columns = [
            'name', 'code', 'category_id', 'brand_id', 'model_id', 'unit_id',
            'rack_id', 'shelf_id', 'purchase_price', 'sale_price', 'mrp',
            'discount_percentage', 'stock_quantity', 'low_stock_alert',
            'description', 'is_active'
        ];

        $sampleData = [
            [
                'Sample Product', 'PRD001', '1', '1', '1', '1',
                '1', '1', '100.00', '150.00', '200.00',
                '10', '50', '5',
                'Sample product description', '1'
            ]
        ];

        $callback = function() use ($columns, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Process bulk product import
     */
    public function processBulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        $imported = 0;
        $skipped = 0;
        $errors = [];

        try {
            if ($extension === 'csv') {
                $handle = fopen($file->getPathname(), 'r');
                $header = fgetcsv($handle);

                while (($row = fgetcsv($handle)) !== false) {
                    $data = array_combine($header, $row);

                    // Validate required fields
                    if (empty($data['name']) || empty($data['code']) || empty($data['purchase_price']) || empty($data['sale_price'])) {
                        $skipped++;
                        continue;
                    }

                    try {
                        Product::create([
                            'name' => $data['name'],
                            'code' => $data['code'],
                            'category_id' => $data['category_id'] ?? null,
                            'brand_id' => $data['brand_id'] ?? null,
                            'model' => $data['model_id'] ?? null,
                            'unit_id' => $data['unit_id'] ?? null,
                            'rack_id' => $data['rack_id'] ?? null,
                            'shelf_id' => $data['shelf_id'] ?? null,
                            'purchase_price' => $data['purchase_price'],
                            'sale_price' => $data['sale_price'],
                            'price' => $data['sale_price'],           // legacy column
                            'cost' => $data['purchase_price'],        // legacy column
                            'mrp' => $data['mrp'] ?? $data['sale_price'],
                            'discount_percentage' => $data['discount_percentage'] ?? 0,
                            'stock_quantity' => $data['stock_quantity'] ?? 0,
                            'min_stock' => $data['low_stock_alert'] ?? 5,       // legacy column
                            'min_stock_level' => $data['low_stock_alert'] ?? 5,
                            'low_stock_alert' => $data['low_stock_alert'] ?? 5,
                            'description' => $data['description'] ?? null,
                            'is_active' => isset($data['is_active']) ? $data['is_active'] : true,
                        ]);
                        $imported++;
                    } catch (\Exception $e) {
                        $errors[] = "Error importing product '{$data['name']}': " . $e->getMessage();
                        $skipped++;
                    }
                }
                fclose($handle);
            }

            $message = "Import completed! {$imported} products imported, {$skipped} skipped.";
            if (count($errors) > 0) {
                $message .= " Errors: " . implode(', ', array_slice($errors, 0, 3));
            }

            return redirect()->back()
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error processing file: ' . $e->getMessage());
        }
    }

    /**
     * Display expired products (expire_date < today)
     */
    public function expired()
    {
        $today = now()->format('Y-m-d');
        $products = Product::with(['category', 'brand'])
            ->whereNotNull('expire_date')
            ->where('expire_date', '<', $today)
            ->orderBy('expire_date', 'asc')
            ->paginate(20);

        return view('admin.products.expired', compact('products'));
    }

    /**
     * POS product search with all required fields
     */
    public function posSearch(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');

        $query = Product::where('is_active', true);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        $products = $query->limit(50)->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code ?? '-',
                'batch' => $product->batch ?? '-',
                'unit' => $product->unit ?? 'pcs',
                'price' => $product->price,
                'sale_price' => $product->sale_price ?? $product->price,
                'stock_quantity' => $product->stock_quantity,
                'discount_percentage' => $product->discount_percentage ?? 0,
                'tax_rate' => $product->tax_rate ?? 0,
                'image_url' => $product->image_url,
                'category' => $product->category,
                'brand' => $product->brand,
            ];
        });

        return response()->json($products);
    }
}
