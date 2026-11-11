<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LabelPrintController extends Controller
{
    /**
     * GET /printlabels
     * Stock-style list (same details as /stock) with pagination for label selection.
     * Products WITH variations expand to one row per variation — exactly like StockController.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $hasProductUnits = Schema::hasTable('product_units');

        $products = Product::query()
            ->with(['unit', 'productVariations', ...($hasProductUnits ? ['productUnits'] : [])])
            ->when($search, fn ($q) => $this->applySearch($q, $search))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $rows = $this->buildStockRows($products->getCollection());

        return view('labels.print', compact('products', 'search', 'rows'));
    }

    /**
     * GET /printlabels/search
     * AJAX live search — returns stock-style rows as JSON.
     */
    public function search(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $page = $request->get('page', 1);
        $hasProductUnits = Schema::hasTable('product_units');

        $products = Product::query()
            ->with(['unit', 'productVariations', ...($hasProductUnits ? ['productUnits'] : [])])
            ->when($search, fn ($q) => $this->applySearch($q, $search))
            ->orderBy('name')
            ->paginate(15, ['*'], 'page', $page)
            ->withQueryString();

        return response()->json([
            'data' => $this->buildStockRows($products->getCollection())->values(),
            'total' => $products->total(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
        ]);
    }

    /**
     * Search across product fields + variation fields (same scope as stock list).
     */
    private function applySearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhereHas('productVariations', fn ($qv) => $qv
                    ->where('variation_value', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%"));
        });
    }

    /**
     * Expand products into stock-style rows (mirrors StockController::index).
     * Each row: key, product_id, variation_id, name, image_url, product_code,
     * variation_label, scan_barcode, stock_quantity, unit, price, price_fmt,
     * status, max_qty.
     */
    private function buildStockRows($products)
    {
        $rows = collect();

        foreach ($products as $p) {
            $productCode = $p->code ?? $p->sku ?? '-';
            $unitName = optional($p->unit)->name ?? 'pcs';
            $imageUrl = $p->image_url ?? asset('images/no-product.png');
            $units = $p->productUnits ?? collect();
            $printable = $units
                ->reject(fn ($u) => in_array(strtolower((string) $u->status), ['sold', 'damaged'], true))
                ->values();

            if (($p->productVariations ?? collect())->isEmpty()) {
                $qty = (int) ($p->stock_quantity ?? 0);
                $rows->push([
                    'key' => 'p' . $p->id,
                    'product_id' => $p->id,
                    'variation_id' => null,
                    'name' => $p->name,
                    'image_url' => $imageUrl,
                    'product_code' => $productCode,
                    'variation_label' => null,
                    'scan_barcode' => $p->barcode ?: ($p->sku ?: ($productCode ?: '-')),
                    'barcode' => $p->barcode ?? '',
                    'stock_quantity' => $qty,
                    'unit' => $unitName,
                    'price' => number_format((float) ($p->sale_price ?? $p->price ?? 0), 2),
                    'status' => $qty <= 0 ? 'Out of Stock' : ($qty <= 5 ? 'Low Stock' : 'In Stock'),
                    'max_qty' => $printable->isNotEmpty() ? $printable->count() : max(1, $qty),
                ]);
                continue;
            }

            foreach ($p->productVariations as $v) {
                $qty = (int) ($v->stock ?? 0);
                $varUnits = $printable->where('product_variation_id', $v->id)->values();
                // Units without variation link (legacy) fall back to product pool only
                // when no variation-linked units exist at all.
                if ($varUnits->isEmpty() && $printable->whereNull('product_variation_id')->isNotEmpty()
                    && $printable->whereNotNull('product_variation_id')->isEmpty()) {
                    $varUnits = $printable;
                }
                $rows->push([
                    'key' => 'p' . $p->id . 'v' . $v->id,
                    'product_id' => $p->id,
                    'variation_id' => $v->id,
                    'name' => $p->name,
                    'image_url' => $imageUrl,
                    'product_code' => $productCode,
                    'variation_label' => $v->variation_value ?? 'Variant',
                    'scan_barcode' => $v->barcode ?: ($v->product_code ?: ($p->barcode ?? null) ?: ($p->sku ?? null) ?: ($productCode ?: '-')),
                    'barcode' => $v->barcode ?: ($p->barcode ?? ''),
                    'stock_quantity' => $qty,
                    'unit' => $unitName,
                    'price' => number_format((float) ($v->sale_price ?? $v->price ?? $p->sale_price ?? $p->price ?? 0), 2),
                    'status' => $qty <= 0 ? 'Out of Stock' : ($qty <= 5 ? 'Low Stock' : 'In Stock'),
                    'max_qty' => $varUnits->isNotEmpty() ? $varUnits->count() : max(1, $qty),
                ]);
            }
        }

        return $rows->values();
    }

    /**
     * POST /printlabels/generate
     * Receive selected products + quantities + settings → clean print view.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'products'               => 'required|array|min:1',
            'products.*.id'          => 'required|integer|exists:products,id',
            'products.*.variation_id' => 'nullable|integer|exists:product_variations,id',
            'products.*.quantity'    => 'required|integer|min:1|max:500',
            'settings.columns'     => 'required|in:1,2,3,4',
            'settings.label_size'  => 'required|in:small,medium,large',
            'settings.font_size'   => 'required|in:small,normal,large',
        ]);

        $relations = ['productVariations', 'brand', 'category', 'color', 'size'];
        if (Schema::hasTable('product_units')) {
            $relations[] = 'productUnits.product';
            $relations[] = 'productUnits.variation';
        }

        // Lowercased lookup maps to split "Color - Size" variation strings.
        $colorNames = Color::pluck('name')
            ->mapWithKeys(fn ($n) => [mb_strtolower(trim((string) $n)) => true]);
        $sizeNames = Size::pluck('name')
            ->mapWithKeys(fn ($n) => [mb_strtolower(trim((string) $n)) => true]);

        $ids     = collect($request->input('products'))->pluck('id');
        $dbProds = Product::with($relations)
                          ->whereIn('id', $ids)
                          ->get()
                          ->keyBy('id');

        $labels = [];

        foreach ($request->input('products') as $item) {
            $product = $dbProds->get($item['id']);
            if (!$product) continue;

            $brandName    = $product->brand?->name ?? '';
            $categoryName = $product->category?->name ?? $product->name;
            $variationId = !empty($item['variation_id']) ? (int) $item['variation_id'] : null;
            $selectedVariation = $variationId
                ? ($product->productVariations?->firstWhere('id', $variationId)
                    ?? \App\Models\ProductVariation::find($variationId))
                : null;
            $availableUnits = ($product->productUnits ?? collect())
                ->reject(fn ($unit) => in_array(strtolower((string) $unit->status), ['sold', 'damaged'], true))
                ->when($variationId, fn ($c) => $c->where('product_variation_id', $variationId))
                ->values();
            if ($availableUnits->isEmpty() && $product->productUnits?->isNotEmpty()) {
                $availableUnits = $product->productUnits
                    ->when($variationId, fn ($c) => $c->where('product_variation_id', $variationId))
                    ->values();
            }
            // Legacy units without variation link: fall back to product pool
            // when the variation has no linked units of its own.
            if ($variationId && $availableUnits->isEmpty()
                && ($product->productUnits ?? collect())->whereNull('product_variation_id')->isNotEmpty()
                && ($product->productUnits ?? collect())->whereNotNull('product_variation_id')->isEmpty()) {
                $availableUnits = ($product->productUnits ?? collect())
                    ->reject(fn ($unit) => in_array(strtolower((string) $unit->status), ['sold', 'damaged'], true))
                    ->values();
            }

            if ($availableUnits->isEmpty()) {
                // No trackable units (bulk import / zero stock) — print
                // product-level labels so the name/barcode still appear.
                $fallbackQty  = min(max((int) $item['quantity'], 1), 500);
                $fallbackCode = $selectedVariation->product_code
                    ?? $product->code ?? $product->sku ?? '—';
                $fallbackBarcode = $selectedVariation->barcode ?? $product->barcode ?? '';
                // Raw float here — the view does the single number_format().
                // Pre-formatting ("1,300.00") would break the view's (float) cast.
                $fallbackPrice = (float) ($selectedVariation->sale_price
                    ?? $selectedVariation->price
                    ?? $product->sale_price ?? $product->price ?? 0);
                [$fbColor, $fbSize] = $this->splitVariationValue(
                    $selectedVariation?->variation_value, $colorNames, $sizeNames
                );
                for ($i = 0; $i < $fallbackQty; $i++) {
                    $labels[] = [
                        'name'        => $product->name,
                        'brand'       => $brandName,
                        'category'    => $categoryName,
                        'variation'   => $selectedVariation?->variation_value,
                        'color'       => $fbColor ?? $product->color?->name,
                        'size'        => $fbSize ?? $product->size?->name,
                        'sku'         => $fallbackCode,
                        'barcode'     => $fallbackBarcode,
                        'scan_value'  => $fallbackBarcode,
                        'scan_format' => 'C128',
                        'price'       => $fallbackPrice,
                        'sale_price'  => $fallbackPrice,
                        'code'        => $fallbackCode,
                        'product_code' => $fallbackCode,
                    ];
                }
                continue;
            }

            $quantity = min((int) $item['quantity'], $availableUnits->count());

            for ($i = 0; $i < $quantity; $i++) {
                $unit = $availableUnits->get($i);
                $unitProduct = $unit->product ?: $product;
                $productCode = $unitProduct->code ?? $unitProduct->sku ?? '';
                $barcode = $unitProduct->barcode;
                // Sell price: this unit's variation price first, then the
                // product's sale_price (the legacy price column may be stale).
                $variation = $unit->variation;
                $unitPrice = ($variation && (float) $variation->price > 0)
                    ? (float) $variation->price
                    : (float) ($product->sale_price ?? $product->price ?? 0);
                [$unitColor, $unitSize] = $this->splitVariationValue(
                    $variation?->variation_value, $colorNames, $sizeNames
                );

                $labels[] = [
                    'name'        => $unitProduct->name,
                    'brand'       => $brandName,
                    'category'    => $categoryName,
                    'variation'   => $variation?->variation_value,
                    'color'       => $unitColor,
                    'size'        => $unitSize,
                    'sku'         => $productCode,
                    'barcode'     => $barcode,
                    'scan_value'  => $barcode,
                    'scan_format' => 'C128',
                    'price'       => $unitPrice,
                    'sale_price'  => $unitPrice,
                    'code'        => $productCode,
                    'product_code' => $productCode,
                ];
            }
        }

        $settings = [
            'columns'     => (int) $request->input('settings.columns', 1),
            'label_size'  => $request->input('settings.label_size', 'medium'),
            'show_name'   => $request->boolean('settings.show_name', true),
            'show_price'  => $request->boolean('settings.show_price', true),
            'show_sku'    => $request->boolean('settings.show_sku', true),
            'font_size'   => $request->input('settings.font_size', 'normal'),
        ];

        if ($request->input('output') === 'pdf') {
            $pdf = Pdf::loadView('labels.generate', compact('labels', 'settings'));
            $pdf->setPaper([0, 0, 164.41, 9999], 'portrait');
            $filename = 'labels-' . now()->format('Ymd_His') . '-' . uniqid() . '.pdf';
            return $pdf->stream($filename);
        }

        return view('labels.generate', compact('labels', 'settings'));
    }

    /**
     * Split a "Color - Size" variation string into [color, size].
     * Single values are matched against the colors/sizes tables.
     */
    private function splitVariationValue(?string $value, $colorNames, $sizeNames): array
    {
        $value = trim((string) $value);
        if ($value === '' || strcasecmp($value, 'Default') === 0) {
            return [null, null];
        }

        $parts = array_map('trim', explode(' - ', $value));
        $parts = array_values(array_filter($parts, fn ($p) => $p !== ''));

        if (count($parts) >= 2) {
            return [$parts[0], $parts[1]];
        }

        $lower = mb_strtolower($parts[0]);
        if (isset($sizeNames[$lower]) && ! isset($colorNames[$lower])) {
            return [null, $parts[0]];
        }

        return [$parts[0], null];
    }
}
