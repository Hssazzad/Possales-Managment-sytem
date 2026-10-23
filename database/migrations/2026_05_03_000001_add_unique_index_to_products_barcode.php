<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->where('barcode', '')
            ->update(['barcode' => null]);

        $this->backfillUniqueBarcodes();

        Schema::table('products', function (Blueprint $table) {
            $table->unique('barcode', 'products_barcode_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_barcode_unique');
        });
    }

    private function backfillUniqueBarcodes(): void
    {
        $products = DB::table('products')
            ->select('id', 'barcode')
            ->orderBy('id')
            ->get();

        $originalCounts = $products
            ->pluck('barcode')
            ->filter(fn ($barcode) => $barcode !== null && trim((string) $barcode) !== '')
            ->map(fn ($barcode) => trim((string) $barcode))
            ->countBy()
            ->all();

        $used = [];

        foreach ($products as $product) {
            $barcode = $product->barcode === null ? null : trim((string) $product->barcode);

            if ($barcode === '' || $barcode === null || isset($used[$barcode])) {
                $barcode = $this->generateBarcodeForId((int) $product->id, $used, $originalCounts);

                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['barcode' => $barcode]);
            }

            $used[$barcode] = true;
        }
    }

    private function generateBarcodeForId(int $id, array $used, array $originalCounts): string
    {
        foreach (range(20, 29) as $prefix) {
            $base = (string) $prefix.str_pad((string) $id, 10, '0', STR_PAD_LEFT);
            $barcode = $base.$this->ean13Checksum($base);

            if (! isset($used[$barcode]) && ! isset($originalCounts[$barcode])) {
                return $barcode;
            }
        }

        throw new RuntimeException("Unable to generate a unique barcode for product ID {$id}.");
    }

    private function ean13Checksum(string $base): int
    {
        $sum = 0;

        foreach (str_split($base) as $index => $digit) {
            $sum += (int) $digit * ($index % 2 === 0 ? 1 : 3);
        }

        return (10 - ($sum % 10)) % 10;
    }
};
