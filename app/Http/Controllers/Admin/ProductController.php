<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\GeneratesProductIdentifiers;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Model as ProductModel;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\ProductStock;
use App\Models\ProductUnit;
use App\Models\ProductVariation;
use App\Models\Rack;
use App\Models\Shelf;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    use GeneratesProductIdentifiers;
    // =========================================================================
    // INDEX — Product list with search + quick-edit dropdown data
    // =========================================================================

    public function index(Request $request): View
    {
        $search  = $request->get('search', '');
        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = [
            'created_at'     => 'created_at',
            'barcode'        => 'barcode',
            'code'           => 'code',
            'name'           => 'name',
            'sale_price'     => 'sale_price',
            'stock_quantity' => 'stock_quantity',
        ];

        $sortColumn      = $allowedSorts[$sortBy] ?? 'created_at';
        $hasProductUnits = Schema::hasTable('product_units');

        // Align child relations sorting with the selected column and direction
        $unitSortCol = in_array($sortColumn, ['barcode', 'created_at'])
            ? $sortColumn
            : ($sortColumn === 'code' ? 'product_code' : 'id');

        $variationSortCol = in_array($sortColumn, ['barcode', 'created_at'])
            ? $sortColumn
            : ($sortColumn === 'code' ? 'product_code' : 'id');

        $relations = [
            'category', 'brand', 'supplier', 'unit', 'rack', 'shelf', 'stocks',
            'productVariations' => fn ($q) => $q->orderBy($variationSortCol, $sortDir)->orderBy('id', $sortDir),
        ];

        if ($hasProductUnits) {
            $relations['productUnits'] = fn ($q) => $q->orderBy($unitSortCol, $sortDir)->orderBy('id', $sortDir);
            $relations['productUnits.variation'] = fn ($q) => $q;
        }

        // Base query with search filter
        $query = Product::query()
            ->when($search, function ($q) use ($search, $hasProductUnits) {
                $q->where(function ($sq) use ($search, $hasProductUnits) {
                    $sq->where('name',    'like', "%{$search}%")
                      ->orWhere('code',    'like', "%{$search}%")
                      ->orWhere('sku',     'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%")
                      ->orWhereHas('productVariations', function ($vsq) use ($search) {
                          $vsq->where('variation_value', 'like', "%{$search}%")
                             ->orWhere('product_code', 'like', "%{$search}%")
                             ->orWhere('barcode', 'like', "%{$search}%");
                      });

                    if ($hasProductUnits) {
                        $sq->orWhereHas('productUnits', function ($usq) use ($search) {
                            $usq->where('product_code', 'like', "%{$search}%")
                               ->orWhere('barcode', 'like', "%{$search}%");
                        });
                    }
                });
            });

        $perPage = 20;
        $products = (clone $query)
            ->with($relations)
            ->orderBy($sortColumn, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        $totalProducts = $products->total();

        // Calculate rows on current page
        $displayedRows = $products->getCollection()->sum(function ($product) use ($hasProductUnits) {
            $unitCount = $hasProductUnits
                ? ($product->productUnits ?? collect())->where('status', 'available')->count()
                : 0;
            if ($unitCount > 0) {
                return $unitCount;
            }

            $variationCount = ($product->productVariations ?? collect())->count();

            return $variationCount > 0 ? $variationCount : 1;
        });

        // Calculate total product code rows across all matching products
        $allMatchingProducts = (clone $query)
            ->with([
                'productVariations' => fn ($q) => $q->select('id', 'product_id'),
            ])
            ->when($hasProductUnits, function ($q) {
                $q->with(['productUnits' => fn ($uq) => $uq->where('status', 'available')->select('id', 'product_id')]);
            })
            ->get();

        $totalProductCodeRows = $allMatchingProducts->sum(function ($product) use ($hasProductUnits) {
            $unitCount = $hasProductUnits
                ? ($product->productUnits ?? collect())->count()
                : 0;
            if ($unitCount > 0) {
                return $unitCount;
            }

            $variationCount = ($product->productVariations ?? collect())->count();

            return $variationCount > 0 ? $variationCount : 1;
        });

        // Continuous serial numbering across pagination pages
        $startRowNum = 1;
        if ($products->currentPage() > 1) {
            $skipCount = ($products->currentPage() - 1) * $perPage;
            $previousPageProducts = (clone $query)
                ->orderBy($sortColumn, $sortDir)
                ->take($skipCount)
                ->with([
                    'productVariations' => fn ($q) => $q->select('id', 'product_id'),
                ])
                ->when($hasProductUnits, function ($q) {
                    $q->with(['productUnits' => fn ($uq) => $uq->where('status', 'available')->select('id', 'product_id')]);
                })
                ->get();

            $startRowNum = $previousPageProducts->sum(function ($product) use ($hasProductUnits) {
                $unitCount = $hasProductUnits
                    ? ($product->productUnits ?? collect())->count()
                    : 0;
                if ($unitCount > 0) {
                    return $unitCount;
                }

                $variationCount = ($product->productVariations ?? collect())->count();

                return $variationCount > 0 ? $variationCount : 1;
            }) + 1;
        }

        // Data for the Quick-Edit modal dropdowns
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands     = Brand::where('is_active', true)->orderBy('name')->get();
        $units      = Unit::where('is_active', true)->orderBy('name')->get();
        $colors     = Color::where('is_active', true)->orderBy('name')->get();
        $sizes      = Size::where('is_active', true)->orderBy('name')->get();
        $racks      = Rack::where('is_active', true)->orderBy('name')->get();
        $shelves    = Shelf::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.index', compact(
            'products', 'search', 'sortBy', 'sortDir',
            'displayedRows', 'totalProducts', 'totalProductCodeRows', 'startRowNum',
            'categories', 'brands', 'units', 'colors', 'sizes', 'racks', 'shelves',
            'hasProductUnits'
        ));
    }

    /**
     * JSON for the product list "Quick Edit" modal. Keeps heavy payloads out of HTML
     * (avoids document/HTML line corruption and stray SyntaxError reports).
     *
     * Register in routes/web.php (inside your admin group):
     * Route::get('products/{product}/quick-edit-data', [ProductController::class, 'quickEditData'])->name('products.quick-edit-data');
     */
    public function quickEditData(Product $product): JsonResponse
    {
        $product->load(['category', 'brand', 'supplier', 'unit', 'rack', 'shelf', 'stocks', 'productVariations']);

        return response()->json($product->toArray());
    }

    // =========================================================================
    // CREATE
    // =========================================================================

    public function create(): View
    {
        $brands      = Brand::where('is_active', true)->orderBy('name')->get();
        $categories  = Category::where('is_active', true)->orderBy('name')->get();
        $units       = Unit::where('is_active', true)->orderBy('name')->get();
        $racks       = Rack::where('is_active', true)->orderBy('name')->get();
        $shelves     = Shelf::where('is_active', true)->with('rack')->orderBy('name')->get();
        $suppliers   = Supplier::active()->orderBy('name')->get();
        $models      = ProductModel::where('is_active', true)->with('brand')->orderBy('name')->get();
        $colors      = Color::all();
        $sizes       = Size::all();
        $pair        = $this->generateNextIdentifierPair();
        $nextBarcode = $pair['barcode'];
        $nextCode    = $pair['product_code'];

        return view('admin.products.create', compact(
            'brands', 'categories', 'units', 'racks', 'shelves',
            'suppliers', 'models', 'nextBarcode', 'nextCode', 'colors', 'sizes'
        ));
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'code'                => 'nullable|string|max:50',
            'barcode'             => 'required|string|max:50|unique:products,barcode',
            'category_id'         => 'nullable|integer|exists:categories,id',
            'brand_id'            => 'nullable|integer|exists:brands,id',
            'supplier_id'         => 'required|integer|exists:suppliers,id',
            'model_id'            => 'nullable|integer',
            'rack_id'             => 'nullable|integer',
            'shelf_id'            => 'nullable|integer',
            'unit_id'             => 'nullable|integer',
            'size_id'             => 'nullable|integer|exists:sizes,id',
            'color_id'            => 'nullable|integer|exists:colors,id',
            'pricing_type'        => 'nullable|string|in:single,batch',
            'purchase_price'      => 'required|numeric|min:0',
            'mrp'                 => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'sale_price'          => 'required|numeric|min:0',
            'stock_quantity'      => 'nullable|integer|min:0',
            'opening_stock'       => 'nullable|integer|min:0',
            'min_stock_level'     => 'nullable|integer|min:0',
            'tax_rate'            => 'nullable|numeric|min:0',
            'description'         => 'nullable|string|max:1000',
            'image'               => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,bmp,svg,tiff,ico|max:5120',
            'manufacturing_date'  => 'nullable|date',
            'expire_date'         => 'nullable|date|after_or_equal:manufacturing_date',
            'is_active'           => 'nullable|boolean',
        ]);

        $supplierId = (int) $validated['supplier_id'];

        if (!Schema::hasColumn('products', 'supplier_id')) {
            unset($validated['supplier_id']);
        }

        $barcodeSeq = $this->extractSequenceFromBarcode($validated['barcode'] ?? null);
        if ($barcodeSeq > 0) {
            $validated['code'] = $this->formatProductCode($barcodeSeq);
            $validated['barcode'] = $this->formatBarcode($barcodeSeq);
        } else {
            $pair = $this->generateNextIdentifierPair();
            $validated['code'] = $pair['product_code'];
            $validated['barcode'] = $pair['barcode'];
        }

        // Sync legacy alias columns
        $validated['is_active']       = $request->boolean('is_active', true);
        $validated['price']           = $validated['sale_price'] ?? 0;
        $validated['cost']            = $validated['purchase_price'] ?? 0;
        $validated['code']            = $this->sanitizeBarcodeCode($validated['code']);
        $validated['sku']             = $validated['code'];
        $validated['barcode']         = $this->sanitizeBarcodeCode($validated['barcode']);

        // The stock_quantity will be derived from variations sum
        $validated['stock_quantity']  = 0;

        // Image upload
        $validated['image'] = $this->handleImageUpload($request);

        DB::beginTransaction();

        try {
        $product = Product::create($validated);
        $supplierPurchaseTotal = 0.0;
        $usedBarcodes = array_filter([$validated['barcode'] ?? null]);
        $usedCodes    = array_filter([$validated['code'] ?? null]);

        // Process Variations & Stock
        if ($request->has('stocks') && is_array($request->stocks)) {
            $totalQty = 0;

            foreach ($request->stocks as $entry) {
                $color_id = !empty($entry['color_id']) ? $entry['color_id'] : null;
                $size_id  = !empty($entry['size_id'])  ? $entry['size_id']  : null;
                $quantity = (int) ($entry['quantity'] ?? 0);
                $entryPurchasePrice = isset($entry['purchase_price']) && is_numeric($entry['purchase_price'])
                    ? (float) $entry['purchase_price']
                    : (float) ($product->purchase_price ?? 0);
                $entrySellPrice = isset($entry['sell_price']) && is_numeric($entry['sell_price'])
                    ? (float) $entry['sell_price']
                    : (float) ($product->sale_price ?? 0);

                if ($quantity < 0) continue;

                $supplierPurchaseTotal += $entryPurchasePrice * $quantity;

                // 1. Create ProductStock entry (Detailed variation stock)
                \App\Models\ProductStock::create([
                    'product_id' => $product->id,
                    'color_id'   => $color_id,
                    'size_id'    => $size_id,
                    'size_name'  => $entry['size_name'] ?? null,
                    'unit_id'    => $product->unit_id,
                    'quantity'   => $quantity,
                ]);

                // 2. Create or update ProductVariation entry (Backward compatibility)
                $colorName = $color_id ? \App\Models\Color::find($color_id)?->name : null;
                $sizeName  = $size_id  ? \App\Models\Size::find($size_id)?->name  : null;
                $vValue    = implode(' - ', array_filter([$colorName, $sizeName])) ?: 'Default';

                // Auto-generate variation barcode cleanly and sequentially
                if (!empty($entry['barcode'])) {
                    $variationBarcode = $this->sanitizeBarcodeCode($entry['barcode']);
                    $seqFromBc = $this->extractSequenceFromBarcode($variationBarcode);
                    if ($seqFromBc > 0 && !$this->productCodeExists($this->formatProductCode($seqFromBc), null, $usedCodes)) {
                        $variationCode = $this->formatProductCode($seqFromBc);
                    } else {
                        $variationCode = $this->generateNextProductCode($usedCodes);
                    }
                } else {
                    $pair = $this->generateNextIdentifierPair(array_merge($usedCodes, $usedBarcodes));
                    $variationCode    = $pair['product_code'];
                    $variationBarcode = $pair['barcode'];
                }
                $usedBarcodes[] = $variationBarcode;
                $usedCodes[]    = $variationCode;

                // Check if same variation already exists
                $existingVariation = \App\Models\ProductVariation::where('product_id', $product->id)
                    ->where('variation_value', $vValue)
                    ->first();

                if ($existingVariation) {
                    $existingVariation->update([
                        'product_code'   => $this->resolveVariationProductCode($existingVariation, $usedCodes),
                        'stock'          => $existingVariation->stock + $quantity,
                        'price'          => $entrySellPrice > 0 ? $entrySellPrice : $existingVariation->price,
                        'purchase_price' => $entryPurchasePrice > 0 ? $entryPurchasePrice : $existingVariation->purchase_price,
                    ]);
                    $variationRecord = $existingVariation->fresh();
                } else {
                    $variationRecord = \App\Models\ProductVariation::create([
                        'product_id'      => $product->id,
                        'variation_type'  => 'Stock',
                        'variation_value' => $vValue,
                        'product_code'    => $variationCode,
                        'barcode'         => $variationBarcode,
                        'stock'           => $quantity,
                        'price'           => $entrySellPrice,
                        'purchase_price'  => $entryPurchasePrice,
                        'is_active'       => 1,
                    ]);
                }

                $this->syncProductUnits($variationRecord, $usedCodes, $usedBarcodes);
                $totalQty += $quantity;
            }

            // Update main product total stock
            $product->update(['stock_quantity' => $totalQty]);

            // For single-variation legacy fields, store the first one if available
            if (count($request->stocks) > 0) {
                $firstStock = $request->stocks[0];
                $product->update([
                    'color_id'       => !empty($firstStock['color_id']) ? $firstStock['color_id'] : null,
                    'size_id'        => !empty($firstStock['size_id'])  ? $firstStock['size_id']  : null,
                    'purchase_price' => !empty($firstStock['purchase_price']) ? (float) $firstStock['purchase_price'] : ($product->purchase_price ?? 0),
                    'sale_price'     => !empty($firstStock['sell_price'])     ? (float) $firstStock['sell_price']     : ($product->sale_price ?? 0),
                    'price'          => !empty($firstStock['sell_price'])     ? (float) $firstStock['sell_price']     : ($product->price ?? 0),
                    'cost'           => !empty($firstStock['purchase_price']) ? (float) $firstStock['purchase_price'] : ($product->cost ?? 0),
                ]);
            }
        }

        // Variant modal variations
        $this->syncVariations($product, $request);
        $this->recordSupplierOpeningDue($supplierId, $product, $supplierPurchaseTotal);

        DB::commit();

        return redirect()->route('products.create')
            ->with('success', 'Product created successfully! Add another one below.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create product: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    public function show(int $id): View
    {
        $product = Product::with('productVariations')->findOrFail($id);

        return view('admin.products.show', compact('product'));
    }

    // =========================================================================
    // EDIT — Full edit page
    // =========================================================================

    public function edit(int $id): View
    {
        $product    = Product::with(['productVariations', 'stocks.color', 'stocks.size', 'supplier', 'color', 'size'])->findOrFail($id);
        $brands     = Brand::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units      = Unit::where('is_active', true)->orderBy('name')->get();
        $racks      = Rack::where('is_active', true)->orderBy('name')->get();
        $shelves    = Shelf::where('is_active', true)->with('rack')->orderBy('name')->get();
        $models     = ProductModel::where('is_active', true)->with('brand')->orderBy('name')->get();
        $colors     = Color::all();
        $sizes      = Size::all();
        $suppliers  = Supplier::active()->orderBy('name')->get();

        return view('admin.products.edit', compact(
            'product', 'brands', 'categories', 'units',
            'racks', 'shelves', 'models', 'colors', 'sizes', 'suppliers'
        ));
    }

    // =========================================================================
    // UPDATE — Handles BOTH full edit page AND quick-edit modal AJAX
    //
    // Key fixes vs original:
    //   1. 'code' and 'barcode' use 'sometimes' so modal (which omits them) works
    //   2. 'rack_id', 'shelf_id', 'size', 'color' are now validated & saved
    //   3. Legacy alias columns (price, cost) are kept in sync
    // =========================================================================

    public function update(Request $request, int $id): mixed
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'code'                => 'sometimes|required|string|max:50|unique:products,code,' . $id,
            'barcode'             => 'sometimes|required|string|max:50|unique:products,barcode,' . $id,
            'category_id'         => 'nullable|integer|exists:categories,id',
            'brand_id'            => 'nullable|integer|exists:brands,id',
            'supplier_id'         => 'nullable|integer|exists:suppliers,id',
            'model_id'            => 'nullable|integer',
            'unit_id'             => 'nullable|integer',
            'rack_id'             => 'nullable|integer',
            'shelf_id'            => 'nullable|integer',
            'size_id'             => 'nullable|integer|exists:sizes,id',
            'color_id'            => 'nullable|integer|exists:colors,id',
            'pricing_type'        => 'nullable|string|in:single,batch',
            'purchase_price'      => 'required|numeric|min:0',
            'mrp'                 => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'sale_price'          => 'required|numeric|min:0',
            'stock_quantity'      => 'nullable|integer|min:0',
            'opening_stock'       => 'nullable|integer|min:0',
            'min_stock_level'     => 'nullable|integer|min:0',
            'tax_rate'            => 'nullable|numeric|min:0',
            'description'         => 'nullable|string|max:1000',
            'image'               => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,bmp,svg,tiff,ico|max:5120',
            'manufacturing_date'  => 'nullable|date',
            'expire_date'         => 'nullable|date|after_or_equal:manufacturing_date',
            'is_active'           => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['price']     = $validated['sale_price'] ?? $product->price ?? 0;
        $validated['cost']      = $validated['purchase_price'] ?? $product->cost ?? 0;

        // Sanitize barcode and code if provided
        if (isset($validated['barcode'])) {
            $validated['barcode'] = $this->sanitizeBarcodeCode($validated['barcode']);
        }
        if (isset($validated['code'])) {
            $validated['code'] = $this->sanitizeBarcodeCode($validated['code']);
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                $oldPath = public_path('storage/' . $product->image);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $validated['image'] = $this->handleImageUpload($request);
        }

        $product->update($validated);

        // Update individual variation stock from edit form
        if ($request->has('variations') && is_array($request->variations)) {
            foreach ($request->variations as $variationId => $data) {
                $variation = ProductVariation::find($variationId);
                if ($variation && $variation->product_id == $product->id) {
                    $stock = (int) ($data['stock'] ?? 0);
                    if ($stock >= 0) {
                        $variation->update(['stock' => $stock]);
                        $this->syncProductUnits($variation->fresh());
                    }
                }
            }
            $totalStock = ProductVariation::where('product_id', $product->id)->sum('stock');
            $product->update(['stock_quantity' => $totalStock]);
        }

        // Process Variations & Stock Synchronization
        if ($request->has('stocks') && is_array($request->stocks)) {
            // Remove existing variations to ensure fresh sync
            \App\Models\ProductStock::where('product_id', $product->id)->delete();
            ProductUnit::whereIn(
                'product_variation_id',
                ProductVariation::where('product_id', $product->id)->pluck('id')
            )->update(['product_variation_id' => null]);
            \App\Models\ProductVariation::where('product_id', $product->id)->delete();

            $usedBarcodes = array_filter([$product->barcode]);
            $usedCodes    = array_filter([$product->code]);
            $totalQty = 0;

            foreach ($request->stocks as $entry) {
                $color_id = !empty($entry['color_id']) ? $entry['color_id'] : null;
                $size_id  = !empty($entry['size_id']) ? $entry['size_id'] : null;
                $quantity = (int) ($entry['quantity'] ?? 0);

                if ($quantity < 0) continue;

                // 1. Create Detailed ProductStock record
                \App\Models\ProductStock::create([
                    'product_id' => $product->id,
                    'color_id'   => $color_id,
                    'size_id'    => $size_id,
                    'unit_id'    => $product->unit_id,
                    'quantity'   => $quantity,
                ]);

                // 2. Backward Compatibility: Create or update ProductVariation
                $colorName = $color_id ? \App\Models\Color::find($color_id)?->name : null;
                $sizeName  = $size_id  ? \App\Models\Size::find($size_id)?->name : null;
                $vValue    = implode(' - ', array_filter([$colorName, $sizeName])) ?: 'Default';

                // Auto-generate variation barcode cleanly and sequentially
                if (!empty($entry['barcode'])) {
                    $variationBarcode = $this->sanitizeBarcodeCode($entry['barcode']);
                    $seqFromBc = $this->extractSequenceFromBarcode($variationBarcode);
                    if ($seqFromBc > 0 && !$this->productCodeExists($this->formatProductCode($seqFromBc), null, $usedCodes)) {
                        $variationCode = $this->formatProductCode($seqFromBc);
                    } else {
                        $variationCode = $this->generateNextProductCode($usedCodes);
                    }
                } else {
                    $pair = $this->generateNextIdentifierPair(array_merge($usedCodes, $usedBarcodes));
                    $variationCode    = $pair['product_code'];
                    $variationBarcode = $pair['barcode'];
                }
                $usedBarcodes[] = $variationBarcode;
                $usedCodes[]    = $variationCode;

                // Check if same variation already exists
                $existingVariation = \App\Models\ProductVariation::where('product_id', $product->id)
                    ->where('variation_value', $vValue)
                    ->first();

                if ($existingVariation) {
                    $existingVariation->update([
                        'product_code'   => $this->resolveVariationProductCode($existingVariation, $usedCodes),
                        'stock'          => $existingVariation->stock + $quantity,
                        'price'          => !empty($entry['sell_price']) ? (float)$entry['sell_price'] : $existingVariation->price,
                        'purchase_price' => !empty($entry['purchase_price']) ? (float)$entry['purchase_price'] : $existingVariation->purchase_price,
                    ]);
                    $variationRecord = $existingVariation->fresh();
                } else {
                    $variationRecord = \App\Models\ProductVariation::create([
                        'product_id'      => $product->id,
                        'variation_type'  => 'Stock',
                        'variation_value' => $vValue,
                        'product_code'    => $variationCode,
                        'barcode'         => $variationBarcode,
                        'stock'           => $quantity,
                        'price'           => !empty($entry['sell_price']) ? (float)$entry['sell_price'] : (float)($product->sale_price ?? 0),
                        'purchase_price'  => !empty($entry['purchase_price']) ? (float)$entry['purchase_price'] : (float)($product->purchase_price ?? 0),
                        'extra_price'     => 0,
                        'is_active'       => 1,
                    ]);
                }

                $this->syncProductUnits($variationRecord, $usedCodes, $usedBarcodes);
                $totalQty += $quantity;
            }

            // Sync total stock quantity to main product table
            $product->update(['stock_quantity' => $totalQty]);

            // Sync first variation to legacy fields
            if (count($request->stocks) > 0) {
                $firstStock = $request->stocks[0];
                $product->update([
                    'color_id'       => !empty($firstStock['color_id']) ? $firstStock['color_id'] : null,
                    'size_id'        => !empty($firstStock['size_id'])  ? $firstStock['size_id']  : null,
                ]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully!',
                'product' => $product->fresh(['category', 'brand', 'unit', 'rack', 'shelf', 'productVariations']),
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        $product = Product::findOrFail($id);

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        DB::transaction(function () use ($product) {
            ProductStock::where('product_id', $product->id)->delete();
            ProductVariation::where('product_id', $product->id)->delete();

            $product->delete();
        });

        return redirect()->back()->with('success', 'Product deleted successfully!');
    }

    // =========================================================================
    // SEARCH — used by admin product selector (AJAX)
    // =========================================================================

    public function search(Request $request): JsonResponse
    {
        $search = $request->get('search', '');
        $hasProductUnits = Schema::hasTable('product_units');

        $query = Product::where('is_active', true)->with(['unit']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search, $hasProductUnits) {
                $q->where('name',    'like', "%{$search}%")
                  ->orWhere('code',    'like', "%{$search}%")
                  ->orWhere('sku',     'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");

                if ($hasProductUnits) {
                    $q->orWhereHas('productUnits', function ($sq) use ($search) {
                        $sq->where('product_code', 'like', "%{$search}%")
                           ->orWhere('barcode', 'like', "%{$search}%");
                    });
                }
            });
        }

        $products = $query->limit(10)->get()->map(function ($product) {
            return [
                'id'             => $product->id,
                'name'           => $product->name,
                'code'           => $product->code,
                'barcode'        => $product->barcode,
                'sale_price'     => (float) ($product->sale_price ?? 0),
                'cost'           => (float) ($product->cost ?? 0),
                'purchase_price' => (float) ($product->purchase_price ?? 0),
                'stock_quantity' => (int)   ($product->stock_quantity ?? 0),
                'unit'           => $product->unit?->name ?? null,
                'image'          => $product->image_url, // uses model accessor
            ];
        });

        return response()->json($products);
    }

    // =========================================================================
    // POS SEARCH — used by the POS terminal
    // =========================================================================

    public function posSearch(Request $request): JsonResponse
    {
        $search   = $request->get('search', '');
        $category = $request->get('category', 'all');
        $hasProductUnits = Schema::hasTable('product_units');

        $query = Product::query()->where(function ($q) {
            $q->where('is_active', true)->orWhereNull('is_active');
        });

        if (!empty($search)) {
            $query->where(function ($q) use ($search, $hasProductUnits) {
                $q->where('name',    'like', "%{$search}%")
                  ->orWhere('code',    'like', "%{$search}%")
                  ->orWhere('sku',     'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhereHas('productVariations', function ($sq) use ($search) {
                      $sq->where('product_code', 'like', "%{$search}%")
                         ->orWhere('barcode', 'like', "%{$search}%");
                  });

                if ($hasProductUnits) {
                    $q->orWhereHas('productUnits', function ($sq) use ($search) {
                        $sq->where('product_code', 'like', "%{$search}%")
                           ->orWhere('barcode', 'like', "%{$search}%");
                    });
                }
            });
        }

        if ($category && $category !== 'all') {
            $query->where('category_id', (int) $category);
        }

        $relations = ['category', 'brand', 'unit', 'productVariations'];

        if ($hasProductUnits) {
            $relations[] = 'productVariations.units';
        }

        $products = $query
            ->with($relations)
            ->limit(50)
            ->get()
            ->map(function ($product) use ($hasProductUnits) {
                return [
                    'id'                  => $product->id,
                    'name'                => $product->name,
                    'code'                => $product->code    ?? '-',
                    'sku'                 => $product->sku     ?? null,
                    'barcode'             => $product->barcode ?? null,
                    'batch'               => $product->batch   ?? null,
                    'unit'                => $product->unit?->name ?? 'pcs',
                    'price'               => (float) ($product->sale_price ?? $product->price ?? 0),
                    'sale_price'          => (float) ($product->sale_price ?? $product->price ?? 0),
                    'stock_quantity'      => (int)   ($product->stock_quantity  ?? 0),
                    'discount_percentage' => (float) ($product->discount_percentage ?? 0),
                    'tax_rate'            => (float) ($product->tax_rate ?? 0),
                    'image_url'           => $product->image_url, // uses model accessor
                    'category'            => $product->category
                                                ? ['id' => $product->category->id, 'name' => $product->category->name]
                                                : null,
                    'brand'               => $product->brand
                                                ? ['id' => $product->brand->id, 'name' => $product->brand->name]
                                                : null,
                    'variations'          => $product->productVariations->map(function ($v) use ($hasProductUnits, $product) {
                        $availableUnits = $hasProductUnits
                            ? ($v->units ?? collect())->where('status', 'available')->values()
                            : collect();
                        $firstUnit = $availableUnits->first();

                        return [
                            'id'              => $v->id,
                            'variation_type'  => $v->variation_type,
                            'variation_value' => $v->variation_value,
                            'extra_price'     => (float) ($v->extra_price ?? 0),
                            'stock'           => (int)   ($v->stock ?? 0),
                            'product_code'    => $firstUnit->unit_code ?? $firstUnit->product_code ?? $v->product_code ?? null,
                            'sku'             => $firstUnit->unit_code ?? $firstUnit->product_code ?? $v->product_code ?? null,
                            'barcode'         => $product->barcode,
                            'unit_codes'      => $availableUnits->map(fn ($unit) => $unit->unit_code ?? $unit->product_code)->filter()->values(),
                            'unit_barcodes'   => $availableUnits->map(fn () => $product->barcode)->filter()->values(),
                        ];
                    })->values(),
                ];
            });

        return response()->json($products);
    }

    // =========================================================================
    // EXPORT — CSV download
    // =========================================================================

    public function export()
    {
        $products = Product::with(['category', 'brand', 'unit', 'rack', 'shelf'])->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products_export.csv"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID', 'Name', 'Code', 'Barcode', 'SKU',
                'Category', 'Brand', 'Rack', 'Shelf', 'Unit',
                'Size', 'Color',
                'Purchase Price', 'Sale Price', 'MRP', 'Discount %',
                'Stock', 'Min Stock', 'Tax Rate',
                'Description', 'Status', 'Created At',
            ]);

            foreach ($products as $p) {
                fputcsv($file, [
                    $p->id,
                    $p->name,
                    $p->code,
                    $p->barcode,
                    $p->sku,
                    $p->category?->name ?? '',
                    $p->brand?->name    ?? '',
                    $p->rack?->name     ?? '',
                    $p->shelf?->name    ?? '',
                    $p->unit?->name     ?? '',
                    $p->size  ?? '',
                    $p->color ?? '',
                    $p->purchase_price,
                    $p->sale_price,
                    $p->mrp,
                    $p->discount_percentage,
                    $p->stock_quantity,
                    $p->min_stock_level,
                    $p->tax_rate,
                    $p->description,
                    $p->is_active ? 'Active' : 'Inactive',
                    $p->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================================================================
    // BULK UPLOAD
    // =========================================================================

    public function bulkUpload(): View
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
            'rack_id', 'shelf_id', 'size', 'color',
            'purchase_price', 'sale_price', 'mrp', 'discount_percentage',
            'stock_quantity', 'low_stock_alert', 'description', 'is_active',
        ];

        $sampleData = [[
            'Sample Product', 'PRD001', '1', '1', '1', '1',
            '1', '1', 'M', 'Red',
            '100.00', '150.00', '200.00', '10',
            '50', '5', 'Sample product description', '1',
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

    public function processBulkUpload(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls|max:10240']);

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

                    if (
                        empty($data['name'])           ||
                        empty($data['code'])           ||
                        empty($data['purchase_price']) ||
                        empty($data['sale_price'])
                    ) {
                        $skipped++;
                        continue;
                    }

                    try {
                        Product::create([
                            'name'                => $data['name'],
                            'code'                => $this->sanitizeBarcodeCode($data['code']),
                            'category_id'         => $data['category_id']         ?? null,
                            'brand_id'            => $data['brand_id']            ?? null,
                            'model_id'            => $data['model_id']            ?? null,
                            'unit_id'             => $data['unit_id']             ?? null,
                            'rack_id'             => $data['rack_id']             ?? null,
                            'shelf_id'            => $data['shelf_id']            ?? null,
                            'size'                => $data['size']                ?? null,
                            'color'               => $data['color']               ?? null,
                            'purchase_price'      => $data['purchase_price'],
                            'sale_price'          => $data['sale_price'],
                            'price'               => $data['sale_price'],
                            'cost'                => $data['purchase_price'],
                            'mrp'                 => $data['mrp']                 ?? $data['sale_price'],
                            'discount_percentage' => $data['discount_percentage'] ?? 0,
                            'stock_quantity'      => $data['stock_quantity']      ?? 0,
                            'min_stock'           => $data['low_stock_alert']     ?? 5,
                            'min_stock_level'     => $data['low_stock_alert']     ?? 5,
                            'low_stock_alert'     => $data['low_stock_alert']     ?? 5,
                            'description'         => $data['description']         ?? null,
                            'is_active'           => $data['is_active']           ?? true,
                        ]);

                        $imported++;
                    } catch (\Exception $e) {
                        $errors[] = "Error importing '{$data['name']}': " . $e->getMessage();
                        $skipped++;
                    }
                }

                fclose($handle);
            }

            $message = "Import completed! {$imported} products imported, {$skipped} skipped.";

            if (!empty($errors)) {
                $message .= ' Errors: ' . implode(', ', array_slice($errors, 0, 3));
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error processing file: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // EXPIRED PRODUCTS
    // =========================================================================

    public function expired(): View
    {
        $products = Product::with(['category', 'brand'])
            ->expired()   // uses scopeExpired() on the model
            ->orderBy('expire_date', 'asc')
            ->paginate(20);

        return view('admin.products.expired', compact('products'));
    }

    // =========================================================================
    // BARCODE SVG
    // =========================================================================

    public function getBarcodeSvg(string $code)
    {
        $height   = 40;
        $barWidth = 2;

        $patterns = [
            '0' => '0001101', '1' => '0011001', '2' => '0010011',
            '3' => '0111101', '4' => '0100011', '5' => '0110001',
            '6' => '0101111', '7' => '0111011', '8' => '0110111',
            '9' => '0001011',
        ];

        $barcodeSequence = '00000101';

        foreach (str_split((string) $code) as $char) {
            if (isset($patterns[$char])) {
                $barcodeSequence .= $patterns[$char];
            }
        }

        $barcodeSequence .= '10100000';
        $totalWidth       = strlen($barcodeSequence) * $barWidth;

        $svg = '<svg width="100%" height="' . $height . '" viewBox="0 0 ' . $totalWidth . ' ' . $height . '" '
             . 'preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" style="background:#fff;">';

        $xPos = 0;

        foreach (str_split($barcodeSequence) as $bit) {
            if ($bit === '1') {
                $svg .= '<rect x="' . $xPos . '" y="0" width="' . $barWidth . '" height="' . $height . '" fill="#000" />';
            }
            $xPos += $barWidth;
        }

        $svg .= '</svg>';

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function recordSupplierOpeningDue(int $supplierId, Product $product, float $purchaseTotal): void
    {
        $purchaseTotal = round(max(0, $purchaseTotal), 2);

        if ($purchaseTotal <= 0) {
            return;
        }

        $supplier = Supplier::whereKey($supplierId)->lockForUpdate()->first();

        if (!$supplier) {
            return;
        }

        $availableBalance = (float) ($supplier->balance ?? 0);
        $paidFromBalance = min($availableBalance, $purchaseTotal);
        $dueAmount = max(0, $purchaseTotal - $paidFromBalance);

        Purchase::create([
            'supplier_id'     => $supplier->id,
            'invoice_number'  => $this->productPurchaseInvoiceNumber($product),
            'payment_method'  => 'credit',
            'payment_status'  => $this->purchaseStatusFromAmounts($paidFromBalance, $dueAmount),
            'subtotal'        => $purchaseTotal,
            'tax_amount'      => 0,
            'discount_amount' => 0,
            'total_amount'    => $purchaseTotal,
            'paid_amount'     => $paidFromBalance,
            'due_amount'      => $dueAmount,
            'change_amount'   => 0,
            'notes'           => 'Opening stock from product create: ' . $product->name,
            'user_id'         => auth()->id(),
        ]);

        $supplier->total_purchases = (float) ($supplier->total_purchases ?? 0) + $purchaseTotal;
        $supplier->balance = max(0, $availableBalance - $paidFromBalance);
        $supplier->due_amount = max(0, (float) ($supplier->due_amount ?? 0) + $dueAmount);
        $supplier->save();
    }

    private function productPurchaseInvoiceNumber(Product $product): string
    {
        $code = $this->sanitizeBarcodeCode($product->code ?: (string) $product->id) ?: (string) $product->id;

        return 'PRD-' . $code . '-' . $product->id;
    }

    private function purchaseStatusFromAmounts(float $paidAmount, float $dueAmount): string
    {
        if ($dueAmount <= 0) {
            return 'paid';
        }

        return $paidAmount > 0 ? 'partial' : 'pending';
    }

    /**
     * Upload a product image and return the storage path, or null if no file.
     */
    private function handleImageUpload(Request $request): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        $image     = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();

        $destDir = public_path('storage/products');
        if (!file_exists($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $image->move($destDir, $imageName);

        return 'products/' . $imageName;
    }

    /**
     * Delete old variations for a product and re-create from request data.
     * Called from both store() and update().
     */
    private function syncVariations(Product $product, Request $request): void
    {
        if (!$request->has('variations') || !is_array($request->variations)) {
            return;
        }

        // Remove existing variations before re-inserting
        ProductVariation::where('product_id', $product->id)
            ->where('variation_type', 'Variant')
            ->delete();

        $usedCodes    = array_filter([$product->code]);
        $usedBarcodes = array_filter([$product->barcode]);

        foreach ($request->variations as $var) {
            $color = $var['color'] ?? 'N/A';
            $size  = $var['size']  ?? 'N/A';

            $variationValue = ($color !== 'N/A' && $size !== 'N/A')
                ? "{$color} - {$size}"
                : (($color !== 'N/A') ? $color : $size);

            if (!empty($var['barcode'])) {
                $variationBarcode = $this->sanitizeBarcodeCode($var['barcode']);
                $seqFromBc = $this->extractSequenceFromBarcode($variationBarcode);
                if ($seqFromBc > 0 && !$this->productCodeExists($this->formatProductCode($seqFromBc), null, $usedCodes)) {
                    $variationCode = $this->formatProductCode($seqFromBc);
                } else {
                    $variationCode = $this->generateNextProductCode($usedCodes);
                }
            } else {
                $pair = $this->generateNextIdentifierPair(array_merge($usedCodes, $usedBarcodes));
                $variationCode    = $pair['product_code'];
                $variationBarcode = $pair['barcode'];
            }
            $usedCodes[]    = $variationCode;
            $usedBarcodes[] = $variationBarcode;

            $variation = ProductVariation::create([
                'product_id'      => $product->id,
                'variation_type'  => 'Variant',
                'variation_value' => $variationValue,
                'product_code'    => $variationCode,
                'barcode'         => $variationBarcode,
                'stock'           => $var['stock'] ?? 0,
                'extra_price'     => $var['price'] ?? 0,
                'is_active'       => 1,
            ]);

            $this->syncProductUnits($variation, $usedCodes, $usedBarcodes);
        }
    }

    private function syncProductUnits(?ProductVariation $variation, array &$usedCodes = [], array &$usedBarcodes = []): void
    {
        if (!$variation || !Schema::hasTable('product_units')) {
            return;
        }

        $target = max(0, (int) ($variation->stock ?? 0));
        $availableUnits = ProductUnit::where('product_id', $variation->product_id)
            ->where(function ($query) use ($variation) {
                $query->where('product_variation_id', $variation->id)
                    ->orWhereNull('product_variation_id');
            })
            ->where('status', 'available')
            ->orderBy('id')
            ->get();

        ProductUnit::whereIn('id', $availableUnits->pluck('id'))
            ->whereNull('product_variation_id')
            ->update(['product_variation_id' => $variation->id]);

        $availableCount = $availableUnits->count();

        if ($availableCount > $target) {
            $idsToDeactivate = $availableUnits
                ->sortByDesc('id')
                ->take($availableCount - $target)
                ->pluck('id');

            ProductUnit::whereIn('id', $idsToDeactivate)->update([
                'status' => 'damaged',
                'updated_at' => now(),
            ]);

            return;
        }

        if ($availableCount === $target) {
            return;
        }

        $missing = $target - $availableCount;
        $newUnits = ProductUnit::generateUnitCodes(
            $variation->product_id,
            $missing,
            null,
            $variation->id
        );

    }

    private function resolveVariationProductCode(ProductVariation $variation, array $exclude = []): string
    {
        $currentCode = $this->sanitizeBarcodeCode($variation->product_code ?? null);

        if ($currentCode && !$this->productCodeExists($currentCode, $variation->id, $exclude)) {
            return $currentCode;
        }

        return $this->generateNextProductCode($exclude);
    }
}
