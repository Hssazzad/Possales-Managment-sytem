<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Makes the business identifier rule true for existing data:
 *
 *   N products => BB01 .. BBnn   (no gaps, no shared numbers)
 *
 * Two things previously broke that rule:
 *
 *   1. product_variations held their own BB{seq} codes (BB12, BB14, BB16),
 *      competing with products in the same namespace.
 *   2. products.code skipped numbers that variations/units had consumed
 *      (BB01, BB13, BB15).
 *
 * Variations are now identified by id + variation_value only, and the product
 * series is re-packed dense. `products.sku` is kept in step with
 * `products.code` (it is a pure mirror), and the unit barcodes that copy a
 * product barcode are re-mirrored so labels stay consistent.
 *
 * The original values are written to storage/app/identifier-renumber-backup.json
 * so down() can restore them.
 */
return new class extends Migration
{
    private const TEMP_CODE_PREFIX = '__RENUMBER_CODE_';

    private const TEMP_BARCODE_PREFIX = '__RENUMBER_BARCODE_';

    private const BARCODE_BASE = 250000000;

    private string $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/identifier-renumber-backup.json');
    }

    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        $hasSku          = Schema::hasColumn('products', 'sku');
        $hasVariations   = Schema::hasTable('product_variations');
        $hasUnits        = Schema::hasTable('product_units');
        $hasSaleItems    = Schema::hasTable('sale_items') && Schema::hasColumn('sale_items', 'product_code');
        $variationCodeCol = $hasVariations && Schema::hasColumn('product_variations', 'product_code');
        $variationBarCol  = $hasVariations && Schema::hasColumn('product_variations', 'barcode');
        $unitBarcodeCol   = $hasUnits && Schema::hasColumn('product_units', 'barcode');

        // 1. Variations leave the BB namespace entirely.
        if ($variationCodeCol || $variationBarCol) {
            $update = [];
            if ($variationCodeCol) $update['product_code'] = null;
            if ($variationBarCol) $update['barcode'] = null;
            DB::table('product_variations')->update($update);
        }

        // 2. Order products by their current numeric sequence so BB01 keeps the
        //    first slot and the rest follow their existing order.
        $products = DB::table('products')
            ->select(array_filter(['id', 'code', 'barcode', $hasSku ? 'sku' : null]))
            ->get()
            ->sortBy(fn ($p) => [
                $this->sequenceFromCode($p->code) ?: PHP_INT_MAX,
                $p->id,
            ])
            ->values();

        // 3. Back up the original mapping (for down()).
        $backup = [
            'products' => [],
            'units'    => [],
        ];

        foreach ($products as $product) {
            $backup['products'][] = [
                'id'      => $product->id,
                'code'    => $product->code,
                'barcode' => $product->barcode,
                'sku'     => $hasSku ? ($product->sku ?? null) : null,
            ];

            if ($unitBarcodeCol) {
                $backup['units'][$product->id] = DB::table('product_units')
                    ->where('product_id', $product->id)
                    ->pluck('barcode', 'id')
                    ->all();
            }
        }

        file_put_contents($this->backupPath, json_encode($backup, JSON_PRETTY_PRINT));

        // 4. Park every identifier so the unique indexes never see a duplicate
        //    while the numbers shuffle.
        foreach ($products as $product) {
            DB::table('products')->where('id', $product->id)->update([
                'code'    => self::TEMP_CODE_PREFIX . $product->id,
                'barcode' => self::TEMP_BARCODE_PREFIX . $product->id,
            ]);
        }

        // 5. Assign the dense series: BB01, BB02, ... BBnn.
        $moves = [];
        foreach ($products as $index => $product) {
            $sequence = $index + 1;
            $code     = $this->formatCode($sequence);
            $barcode  = 'BB' . (self::BARCODE_BASE + $sequence);

            $update = [
                'code'    => $code,
                'barcode' => $barcode,
            ];
            if ($hasSku) {
                $update['sku'] = $code;
            }

            DB::table('products')->where('id', $product->id)->update($update);

            if ($unitBarcodeCol) {
                // Unit barcodes mirror the parent product barcode by design.
                DB::table('product_units')->where('product_id', $product->id)->update(['barcode' => $barcode]);
            }

            $old = $product->code;
            if ($old !== null && $old !== '' && $old !== $code) {
                $moves[$old] = $code;
            }
        }

        // 6. Keep historical sale rows pointing at the same product.
        if ($hasSaleItems) {
            foreach ($moves as $old => $new) {
                DB::table('sale_items')->where('product_code', $old)->update(['product_code' => $new]);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('products') || !file_exists($this->backupPath)) {
            return;
        }

        $backup = json_decode((string) file_get_contents($this->backupPath), true);
        $hasSku = Schema::hasColumn('products', 'sku');

        foreach ($backup['products'] ?? [] as $row) {
            if (!DB::table('products')->where('id', $row['id'])->exists()) {
                continue;
            }

            $update = ['code' => $row['code'], 'barcode' => $row['barcode']];
            if ($hasSku) {
                $update['sku'] = $row['sku'];
            }

            DB::table('products')->where('id', $row['id'])->update($update);
        }

        foreach ($backup['units'] ?? [] as $productId => $units) {
            foreach ($units as $unitId => $barcode) {
                DB::table('product_units')->where('id', $unitId)->update(['barcode' => $barcode]);
            }
        }

        // Restore the variation codes that this migration cleared? They were
        // competing business identifiers, so they stay cleared on purpose.
    }

    private function formatCode(int $sequence): string
    {
        return $sequence < 10 ? 'BB0' . $sequence : 'BB' . $sequence;
    }

    private function sequenceFromCode(?string $code): int
    {
        if ($code && preg_match('/^BB(\d+)$/i', trim($code), $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }
};
