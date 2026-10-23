<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class StockController extends Controller
{
    /**
     * Stock List — one row per product / product-variation (mirrors
     * LabelPrintController::buildStockRows so counts stay identical).
     * Read-only: no sales / stock / barcode / unit logic is changed here.
     * Each row carries its existing product_units records for the
     * expandable detail section in the Blade view.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $hasProductUnits = Schema::hasTable('product_units');

        $products = Product::query()
            ->with(['unit', 'productVariations', ...($hasProductUnits ? ['productUnits'] : [])])
            ->when($search, fn ($q) => $this->applySearch($q, $search, $hasProductUnits))
            ->orderBy('name', 'asc')
            ->get();

        $rows = $this->buildStockRows($products);

        $totalUnits = (int) $rows->sum('stock_quantity');
        $avgStock = $rows->count() > 0 ? (int) round($rows->avg('stock_quantity')) : 0;

        // Flat payload for the client-side CSV export (main rows only).
        $exportRows = $rows->map(function ($r) {
            return [
                'name' => $r['name'],
                'product_code' => $r['product_code'],
                'variation' => $r['variation_label'],
                'scan_barcode' => $r['scan_barcode'],
                'stock_quantity' => $r['stock_quantity'],
                'unit' => $r['unit'],
                'price' => $r['price'],
            ];
        })->values();

        return view('admin.stock.index', compact('rows', 'search', 'totalUnits', 'avgStock', 'exportRows'));
    }

    /**
     * Low Stock page — the view expects `$all`: product + variation rows
     * with is_variation / variation_label fields. Read-only; stock
     * quantities come straight from the existing stock columns.
     */
    public function lowStock(Request $request)
    {
        $all = $this->getLowStockRows($request);

        return view('admin.stock.low-stock-clean', compact('all'));
    }

    /**
     * CSV download for the Low Stock page (the view links here).
     * Same rows/filters as lowStock(), read-only.
     */
    public function lowStockExport(Request $request)
    {
        $all = $this->getLowStockRows($request);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="low_stock_' . now()->format('Ymd') . '.csv"',
        ];

        $callback = function () use ($all) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['SL.', 'Product', 'Variation', 'Code', 'Stock', 'Unit', 'Price', 'Status']);
            foreach ($all->values() as $i => $r) {
                $qty = (int) ($r->stock_quantity ?? 0);
                fputcsv($file, [
                    $i + 1,
                    $r->name,
                    $r->is_variation ? $r->variation_label : '',
                    $r->code ?? '-',
                    $qty,
                    $r->unit ?? 'pcs',
                    number_format((float) ($r->sale_price ?? $r->price ?? 0), 2),
                    $qty <= 2 ? 'Critical' : 'Low Stock',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Shared query for lowStock() + lowStockExport(): products and their
     * variations with 0 < stock <= 5 (the stub's original thresholds),
     * honoring the view's ?search= and ?filter= parameters.
     */
    private function getLowStockRows(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $filter = $request->get('filter', 'all');
        $hasProductUnits = Schema::hasTable('product_units');

        $products = Product::query()
            ->with(['unit', 'productVariations'])
            ->when($search, fn ($q) => $this->applySearch($q, $search, $hasProductUnits))
            ->orderBy('stock_quantity', 'asc')
            ->get();

        $all = $this->buildLowStockRows($products);

        return match ($filter) {
            'critical' => $all->where('stock_quantity', '<=', 2)->values(),
            'low' => $all->filter(fn ($r) => $r->stock_quantity > 2 && $r->stock_quantity <= 5)->values(),
            'variation' => $all->where('is_variation', true)->values(),
            'product' => $all->where('is_variation', false)->values(),
            default => $all,
        };
    }

    /**
     * Product + variation rows shaped exactly as low-stock-clean expects.
     */
    private function buildLowStockRows($products)
    {
        $rows = collect();

        foreach ($products as $p) {
            $unitName = optional($p->unit)->name ?? 'Piece';
            $price = (float) ($p->sale_price ?? $p->price ?? 0);
            $qty = (int) ($p->stock_quantity ?? 0);

            if ($qty > 0 && $qty <= 5) {
                $rows->push((object) [
                    'id' => $p->id,
                    'name' => $p->name,
                    'image' => $p->image,
                    'code' => $p->code ?? $p->sku ?? '-',
                    'stock_quantity' => $qty,
                    'unit' => $unitName,
                    'price' => $price,
                    'sale_price' => $price,
                    'is_variation' => false,
                    'variation_label' => null,
                ]);
            }

            foreach (($p->productVariations ?? collect()) as $v) {
                $vQty = (int) ($v->stock ?? 0);
                if ($vQty > 0 && $vQty <= 5) {
                    $vPrice = (float) ($v->sale_price ?? $v->price ?? $p->sale_price ?? $p->price ?? 0);
                    $rows->push((object) [
                        'id' => $p->id,
                        'name' => $p->name,
                        'image' => $p->image,
                        'code' => $v->product_code ?: ($p->code ?? $p->sku ?? '-'),
                        'stock_quantity' => $vQty,
                        'unit' => $unitName,
                        'price' => $vPrice,
                        'sale_price' => $vPrice,
                        'is_variation' => true,
                        'variation_label' => $v->variation_value ?? 'Variant',
                    ]);
                }
            }
        }

        return $rows->sortBy('stock_quantity')->values();
    }

    /**
     * Search across product fields + variation fields + unit codes
     * (same scope as LabelPrintController, plus unit_code lookup).
     */
    private function applySearch($query, string $search, bool $hasProductUnits)
    {
        return $query->where(function ($q) use ($search, $hasProductUnits) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhereHas('productVariations', fn ($qv) => $qv
                    ->where('variation_value', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%"));

            if ($hasProductUnits) {
                $q->orWhereHas('productUnits', fn ($uq) => $uq
                    ->where('unit_code', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%"));
            }
        });
    }

    /**
     * Expand products into variation-level stock rows.
     * Same derivation as LabelPrintController::buildStockRows:
     * stock_quantity comes from product / variation stock columns (never
     * recomputed from units). The `units` list is the existing
     * product_units records for that row, ordered by id, with their
     * real database status values.
     */
    private function buildStockRows($products)
    {
        $rows = collect();

        foreach ($products as $p) {
            $productCode = $p->code ?? $p->sku ?? '-';
            $unitName = optional($p->unit)->name ?? 'Piece';
            $imageUrl = $p->image_url ?? asset('images/no-product.png');
            $units = ($p->productUnits ?? collect())->sortBy('id')->values();

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
                    'price' => (float) ($p->sale_price ?? $p->price ?? 0),
                    'price_fmt' => number_format((float) ($p->sale_price ?? $p->price ?? 0), 2),
                    'units' => $units->map(fn ($u) => [
                        'unit_code' => $u->unit_code ?? $u->product_code ?? '-',
                        'status' => $u->status ?? 'available',
                    ])->values(),
                ]);
                continue;
            }

            foreach ($p->productVariations as $v) {
                $qty = (int) ($v->stock ?? 0);
                $varUnits = $units->where('product_variation_id', $v->id)->values();
                // Legacy units without a variation link belong to the product
                // pool — attach them only when no variation-linked units
                // exist at all (same fallback as LabelPrintController).
                if ($varUnits->isEmpty() && $units->whereNull('product_variation_id')->isNotEmpty()
                    && $units->whereNotNull('product_variation_id')->isEmpty()) {
                    $varUnits = $units;
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
                    'price' => (float) ($v->sale_price ?? $v->price ?? $p->sale_price ?? $p->price ?? 0),
                    'price_fmt' => number_format((float) ($v->sale_price ?? $v->price ?? $p->sale_price ?? $p->price ?? 0), 2),
                    'units' => $varUnits->map(fn ($u) => [
                        'unit_code' => $u->unit_code ?? $u->product_code ?? '-',
                        'status' => $u->status ?? 'available',
                    ])->values(),
                ]);
            }
        }

        return $rows->values();
    }
}
