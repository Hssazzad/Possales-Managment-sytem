<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        $products = DB::table('products')->select('id', 'code', 'barcode')->get();
        $updates = $products->filter(fn ($product) => $this->isLegacyGeneratedBarcode($product->barcode)
            && $this->isProductCode($product->code)
            && !$products->contains(fn ($other) => $other->id !== $product->id
                && !$this->isLegacyGeneratedBarcode($other->barcode)
                && $other->barcode === $product->code));

        // Park generated barcodes first so a unique barcode index cannot see a
        // duplicate while BB250000001 becomes BB01.
        foreach ($updates as $product) {
            DB::table('products')->where('id', $product->id)->update([
                'barcode' => '__BARCODE_' . $product->id,
            ]);
        }

        foreach ($updates as $product) {
            DB::table('products')->where('id', $product->id)->update(['barcode' => $product->code]);

            if (Schema::hasTable('product_units') && Schema::hasColumn('product_units', 'barcode')) {
                DB::table('product_units')->where('product_id', $product->id)->update(['barcode' => $product->code]);
            }
        }
    }

    public function down(): void
    {
        // The old long barcode cannot be safely restored after new products are added.
    }

    private function isLegacyGeneratedBarcode(?string $barcode): bool
    {
        return (bool) preg_match('/^BB250000\d+$/i', (string) $barcode);
    }

    private function isProductCode(?string $code): bool
    {
        return (bool) preg_match('/^BB\d+$/i', (string) $code);
    }
};
