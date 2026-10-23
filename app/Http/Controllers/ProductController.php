<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Model as ProductModel;
use App\Models\Product;
use App\Models\ProductVariation;
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
        $brands     = Brand::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units      = Unit::where('is_active', true)->orderBy('name')->get();
        $racks      = Rack::where('is_active', true)->orderBy('name')->get();
        $shelves    = Shelf::where('is_active', true)->with('rack')->orderBy('name')->get();
        $models     = ProductModel::where('is_active', true)->with('brand')->orderBy('name')->get();

        // Next SKU
        $lastProduct = Product::orderBy('id', 'desc')->first();
        $nextNum     = $lastProduct ? ($lastProduct->id + 1) : 1;
        $nextSku     = 'PRD-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        return view('admin.products.create', compact(
            'brands', 'categories', 'units', 'racks', 'shelves', 'models', 'nextSku'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'code'                => 'required|string|max:50|unique:products',
            'category_id'         => 'nullable|integer',
            'brand_id'            => 'nullable|integer',
            'model_id'            => 'nullable|integer',
            'rack_id'             => 'nullable|integer',
            'shelf_id'            => 'nullable|integer',
            'unit_id'             => 'nullable|integer',
            'pricing_type'        => 'nullable|string|in:single,batch',
            'purchase_price'      => 'required|numeric|min:0',
            'mrp'                 => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'sale_price'          => 'required|numeric|min:0',
            'stock_quantity'      => 'required|integer|min:0',
            'min_stock_level'     => 'nullable|integer|min:0',
            'tax_rate'            => 'nullable|numeric|min:0',
            'description'         => 'nullable|string|max:1000',
            'image'               => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'manufacturing_date'  => 'nullable|date',
            'expire_date'         => 'nullable|date|after_or_equal:manufacturing_date',
            'is_active'           => 'nullable|boolean',
            'sku'                 => 'nullable|string|max:100',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['price']     = $validated['sale_price'];
        $validated['cost']      = $validated['purchase_price'];

        // Image upload
        $validated['image'] = null;
        if ($request->hasFile('image')) {
            $image     = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('products', $imageName, 'public');
            $validated['image'] = 'products/' . $imageName;
        }

        // ডাটাবেসে সেভ
        $product = Product::create($validated);
        $this->assignAutoBarcode($product);

        // ✅ Variations save logic
        if ($request->has('variations') && is_array($request->variations)) {
            foreach ($request->variations as $var) {
                $color = $var['color'] ?? 'N/A';
                $size  = $var['size'] ?? 'N/A';
                
                $combo = [];
                if ($color !== 'N/A') $combo[] = $color;
                if ($size !== 'N/A') $combo[] = $size;
                
                $variationValue = empty($combo) ? 'Standard' : implode(' - ', $combo);

                ProductVariation::create([
                    'product_id'      => $product->id,
                    'variation_type'  => 'Variant',
                    'variation_value' => $variationValue,
                    'sku'             => !empty($var['sku']) ? $var['sku'] : null,
                    'stock'           => $var['stock'] ?? 0,
                    'extra_price'     => $var['price'] ?? 0,
                    'is_active'       => 1,
                ]);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully!');
    }

    public function show($id)
    {
        $product = Product::with('productVariations')->findOrFail($id);

        return view('admin.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product    = Product::with('productVariations')->findOrFail($id);
        $brands     = Brand::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units      = Unit::where('is_active', true)->orderBy('name')->get();
        $racks      = Rack::where('is_active', true)->orderBy('name')->get();
        $shelves    = Shelf::where('is_active', true)->with('rack')->orderBy('name')->get();
        $models     = ProductModel::where('is_active', true)->with('brand')->orderBy('name')->get();

        return view('admin.products.edit', compact(
            'product', 'brands', 'categories', 'units', 'racks', 'shelves', 'models'
        ));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'code'                => 'required|string|max:50|unique:products,code,' . $id,
            'category_id'         => 'nullable|integer',
            'brand_id'            => 'nullable|integer',
            'unit_id'             => 'nullable|integer',
            'purchase_price'      => 'required|numeric|min:0',
            'sale_price'          => 'required|numeric|min:0',
            'stock_quantity'      => 'required|integer|min:0',
            'min_stock_level'     => 'nullable|integer|min:0',
            'tax_rate'            => 'nullable|numeric|min:0',
            'description'         => 'nullable|string|max:1000',
            'image'               => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'is_active'           => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['price']     = $validated['sale_price'];
        $validated['cost']      = $validated['purchase_price'];
        $validated['min_stock'] = $validated['min_stock_level'] ?? 0;

        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $image     = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('products', $imageName, 'public');
            $validated['image'] = 'products/' . $imageName;
        } else {
            unset($validated['image']);
        }

        // ডাটাবেস আপডেট
        $product->update($validated);
        $this->assignAutoBarcode($product);

        // ✅ Variations update
        if ($request->has('variations') && is_array($request->variations)) {
            ProductVariation::where('product_id', $product->id)->delete();
            
            foreach ($request->variations as $var) {
                $color = $var['color'] ?? 'N/A';
                $size  = $var['size'] ?? 'N/A';
                
                $combo = [];
                if ($color !== 'N/A') $combo[] = $color;
                if ($size !== 'N/A') $combo[] = $size;
                
                $variationValue = empty($combo) ? 'Standard' : implode(' - ', $combo);

                ProductVariation::create([
                    'product_id'      => $product->id,
                    'variation_type'  => 'Variant',
                    'variation_value' => $variationValue,
                    'sku'             => !empty($var['sku']) ? $var['sku'] : null,
                    'stock'           => $var['stock'] ?? 0,
                    'extra_price'     => $var['price'] ?? 0,
                    'is_active'       => 1,
                ]);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted successfully!']);
    }

    public function search(Request $request)
    {
        $search = $request->get('search', '');

        $query = Product::where('is_active', true)->with(['unit']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->limit(10)->get()->map(function ($product) {
            return [
                'id'             => $product->id,
                'name'           => $product->name,
                'code'           => $product->code,
                'barcode'        => $product->barcode,
                'sale_price'     => $product->sale_price,
                'cost'           => $product->cost,
                'purchase_price' => $product->purchase_price,
                'stock_quantity' => $product->stock_quantity,
                'unit'           => $product->unit ? $product->unit->name : 'pcs',
                'image'          => $product->image ? asset('storage/' . $product->image) : null,
            ];
        });

        return response()->json($products);
    }

    public function export()
    {
        $products = Product::with(['category', 'brand', 'unit'])->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products_export.csv"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID', 'Name', 'Code', 'Barcode', 'Category', 'Brand',
                'Unit', 'Purchase Price', 'Sale Price', 'MRP',
                'Discount %', 'Stock', 'Min Stock', 'Tax Rate', 'Description',
                'Status', 'Created At',
            ]);
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->code,
                    $product->barcode,
                    $product->category ? $product->category->name : '',
                    $product->brand ? $product->brand->name : '',
                    $product->unit ? $product->unit->name : '',
                    $product->purchase_price,
                    $product->sale_price,
                    $product->mrp,
                    $product->discount_percentage,
                    $product->stock_quantity,
                    $product->min_stock_level,
                    $product->tax_rate,
                    $product->description,
                    $product->is_active ? 'Active' : 'Inactive',
                    $product->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkUpload()
    {
        return view('admin.products.bulk-upload');
    }

    public function downloadSample()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_sample.csv"',
        ];

        $columns = [
            'name', 'code', 'category_id', 'brand_id', 'model_id', 'unit_id',
            'rack_id', 'shelf_id', 'purchase_price', 'sale_price', 'mrp',
            'discount_percentage', 'stock_quantity', 'low_stock_alert',
            'description', 'is_active',
        ];

        $sampleData = [[
            'Sample Product', 'PRD001', '1', '1', '1', '1',
            '1', '1', '100.00', '150.00', '200.00',
            '10', '50', '5', 'Sample product description', '1',
        ]];

        $callback = function () use ($columns, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function processBulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
        ]);

        $file      = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $imported  = 0;
        $skipped   = 0;
        $errors    = [];

        try {
            if ($extension === 'csv') {
                $handle = fopen($file->getPathname(), 'r');
                $header = fgetcsv($handle);

                while (($row = fgetcsv($handle)) !== false) {
                    $data = array_combine($header, $row);

                    if (empty($data['name']) || empty($data['code']) || empty($data['purchase_price']) || empty($data['sale_price'])) {
                        $skipped++;
                        continue;
                    }

                    try {
                        $product = Product::create([
                            'name'                => $data['name'],
                            'code'                => $data['code'],
                            'category_id'         => $data['category_id'] ?? null,
                            'brand_id'            => $data['brand_id'] ?? null,
                            'model_id'            => $data['model_id'] ?? null,
                            'unit_id'             => $data['unit_id'] ?? null,
                            'rack_id'             => $data['rack_id'] ?? null,
                            'shelf_id'            => $data['shelf_id'] ?? null,
                            'purchase_price'      => $data['purchase_price'],
                            'sale_price'          => $data['sale_price'],
                            'price'               => $data['sale_price'],
                            'cost'                => $data['purchase_price'],
                            'mrp'                 => $data['mrp'] ?? $data['sale_price'],
                            'discount_percentage' => $data['discount_percentage'] ?? 0,
                            'stock_quantity'      => $data['stock_quantity'] ?? 0,
                            'min_stock'           => $data['low_stock_alert'] ?? 5,
                            'min_stock_level'     => $data['low_stock_alert'] ?? 5,
                            'low_stock_alert'     => $data['low_stock_alert'] ?? 5,
                            'description'         => $data['description'] ?? null,
                            'is_active'           => $data['is_active'] ?? true,
                        ]);
                        $this->assignAutoBarcode($product);
                        $imported++;
                    } catch (\Exception $e) {
                        $errors[] = "Error importing '{$data['name']}': " . $e->getMessage();
                        $skipped++;
                    }
                }
                fclose($handle);
            }

            $message = "Import completed! {$imported} products imported, {$skipped} skipped.";
            if (count($errors) > 0) {
                $message .= ' Errors: ' . implode(', ', array_slice($errors, 0, 3));
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error processing file: ' . $e->getMessage());
        }
    }

    public function expired()
    {
        $today    = now()->format('Y-m-d');
        $products = Product::with(['category', 'brand'])
            ->whereNotNull('expire_date')
            ->where('expire_date', '<', $today)
            ->orderBy('expire_date', 'asc')
            ->paginate(20);

        return view('admin.products.expired', compact('products'));
    }

    // ✅ POS সার্চের মূল ফাংশন (Category, Brand এবং Variations সহ)
    public function posSearch(Request $request)
    {
        $search   = $request->get('search', '');
        $category = $request->get('category', '');

        $query = Product::query()
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($category && $category !== 'all') {
            $query->where('category_id', $category);
        }

        // Relation Data Load (Brand, Category, ProductVariations)
        $products = $query->with(['productVariations', 'category', 'brand'])->limit(50)->get()->map(function ($product) {
            return [
                'id'                  => $product->id,
                'name'                => $product->name,
                'code'                => $product->code ?? '-',
                'sku'                 => $product->sku,
                'barcode'             => $product->barcode,
                'unit'                => $product->unit ? $product->unit->name : 'pcs',
                'price'               => $product->sale_price ?? $product->price,
                'sale_price'          => $product->sale_price ?? $product->price,
                'stock_quantity'      => $product->stock_quantity,
                'discount_percentage' => $product->discount_percentage ?? 0,
                'tax_rate'            => $product->tax_rate ?? 0,
                'image_url'           => $product->image ? asset('storage/' . $product->image) : asset('images/no-product.png'),
                'category'            => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
                'brand'               => $product->brand ? ['id' => $product->brand->id, 'name' => $product->brand->name] : null,
                'variations'          => $product->productVariations
            ];
        });

        return response()->json($products);
    }

    private function assignAutoBarcode(Product $product): void
    {
        if (!empty($product->barcode)) {
            return;
        }

        $product->forceFill([
            'barcode' => $this->generateBarcodeForProduct($product),
        ])->save();
    }

    private function generateBarcodeForProduct(Product $product): string
    {
        $id = (int) $product->getKey();
        foreach (range(20, 29) as $prefix) {
            $base    = (string) $prefix . str_pad((string) $id, 10, '0', STR_PAD_LEFT);
            $barcode = $base . $this->ean13Checksum($base);
            if (!Product::where('barcode', $barcode)->where('id', '<>', $id)->exists()) {
                return $barcode;
            }
        }
        return '2900000000000'; // Fallback
    }

    private function ean13Checksum(string $base): int
    {
        $sum = 0;
        foreach (str_split($base) as $index => $digit) {
            $sum += (int) $digit * ($index % 2 === 0 ? 1 : 3);
        }
        return (10 - ($sum % 10)) % 10;
    }

    public function getBarcodeSvg($code)
    {
        $height   = 40;
        $barWidth = 2;

        $patterns = [
            '0' => '0001101', '1' => '0011001', '2' => '0010011', '3' => '0111101', '4' => '0100011',
            '5' => '0110001', '6' => '0101111', '7' => '0111011', '8' => '0110111', '9' => '0001011',
        ];

        $codeStr          = (string) $code;
        $barcodeSequence  = '00000101';

        for ($i = 0; $i < strlen($codeStr); $i++) {
            $char = $codeStr[$i];
            if (isset($patterns[$char])) {
                $barcodeSequence .= $patterns[$char];
            }
        }

        $barcodeSequence .= '10100000';

        $totalWidth = strlen($barcodeSequence) * $barWidth;
        $svg = '<svg width="100%" height="' . $height . '" viewBox="0 0 ' . $totalWidth . ' ' . $height . '" '
             . 'preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" style="background:#fff;">';

        $xPos = 0;
        for ($j = 0; $j < strlen($barcodeSequence); $j++) {
            if ($barcodeSequence[$j] === '1') {
                $svg .= '<rect x="' . $xPos . '" y="0" width="' . $barWidth . '" height="' . $height . '" fill="#000" />';
            }
            $xPos += $barWidth;
        }

        $svg .= '</svg>';

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }
}