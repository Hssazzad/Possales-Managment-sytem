<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_units')) {
            Schema::create('product_units', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_variation_id')->constrained('product_variations')->cascadeOnDelete();
                $table->string('product_code')->unique();
                $table->string('barcode')->nullable();
                $table->string('status', 20)->default('available');
                $table->timestamp('sold_at')->nullable();
                $table->timestamps();

                $table->index(['product_id', 'status']);
                $table->index(['product_variation_id', 'status']);
            });
        }

        $this->backfillUnitsFromVariations();
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }

    private function backfillUnitsFromVariations(): void
    {
        if (!Schema::hasTable('product_variations') || !Schema::hasTable('product_units')) {
            return;
        }

        $usedCodes = $this->usedValues('code', 'product_code');
        $usedBarcodes = $this->usedValues('barcode', 'barcode');

        DB::table('product_variations')
            ->select('id', 'product_id', 'stock')
            ->orderBy('id')
            ->chunkById(100, function ($variations) use (&$usedCodes, &$usedBarcodes) {
                foreach ($variations as $variation) {
                    $target = max(0, (int) ($variation->stock ?? 0));
                    if ($target === 0) {
                        continue;
                    }

                    $existing = DB::table('product_units')
                        ->where('product_variation_id', $variation->id)
                        ->where('status', 'available')
                        ->count();

                    for ($i = $existing; $i < $target; $i++) {
                        $productCode = $this->nextProductCode($usedCodes);
                        $barcode = $this->nextBarcode($usedBarcodes);

                        DB::table('product_units')->insert([
                            'product_id' => $variation->product_id,
                            'product_variation_id' => $variation->id,
                            'product_code' => $productCode,
                            'barcode' => $barcode,
                            'status' => 'available',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }, 'id');
    }

    private function usedValues(string $productColumn, string $unitColumn): array
    {
        $values = [];

        if (Schema::hasTable('products') && Schema::hasColumn('products', $productColumn)) {
            DB::table('products')
                ->whereNotNull($productColumn)
                ->pluck($productColumn)
                ->each(function ($value) use (&$values) {
                    $values[$this->cleanCode($value)] = true;
                });
        }

        if (Schema::hasTable('product_variations') && Schema::hasColumn('product_variations', $unitColumn)) {
            DB::table('product_variations')
                ->whereNotNull($unitColumn)
                ->pluck($unitColumn)
                ->each(function ($value) use (&$values) {
                    $values[$this->cleanCode($value)] = true;
                });
        }

        if (Schema::hasTable('product_units') && Schema::hasColumn('product_units', $unitColumn)) {
            DB::table('product_units')
                ->whereNotNull($unitColumn)
                ->pluck($unitColumn)
                ->each(function ($value) use (&$values) {
                    $values[$this->cleanCode($value)] = true;
                });
        }

        unset($values['']);

        return $values;
    }

    private function nextProductCode(array &$usedCodes): string
    {
        $prefix = 'BB0';
        $max = 0;

        foreach (array_keys($usedCodes) as $code) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $code, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        do {
            $max++;
            $code = $prefix . $max;
        } while (isset($usedCodes[$code]));

        $usedCodes[$code] = true;

        return $code;
    }

    private function nextBarcode(array &$usedBarcodes): string
    {
        $prefix = 'BB';
        $max = 250000000;

        foreach (array_keys($usedBarcodes) as $barcode) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $barcode, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        do {
            $max++;
            $barcode = $prefix . $max;
        } while (isset($usedBarcodes[$barcode]));

        $usedBarcodes[$barcode] = true;

        return $barcode;
    }

    private function cleanCode(?string $value): string
    {
        return preg_replace('/[^A-Z0-9]/i', '', strtoupper((string) $value));
    }
};
