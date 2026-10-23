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
            return;
        }

        if (!Schema::hasColumn('product_units', 'unit_code')) {
            Schema::table('product_units', function (Blueprint $table) {
                $table->string('unit_code')->nullable()->unique()->after('product_id');
            });
        }

        if (Schema::hasColumn('product_units', 'product_code')) {
            DB::table('product_units')
                ->whereNull('unit_code')
                ->update(['unit_code' => DB::raw('product_code')]);
        }

        foreach (Schema::getIndexes('product_units') as $index) {
            $columns = array_map('strtolower', $index['columns'] ?? []);
            if (($index['unique'] ?? false) && $columns === ['barcode']) {
                Schema::table('product_units', function (Blueprint $table) use ($index): void {
                    $table->dropIndex($index['name']);
                });
            }
        }

        if (Schema::hasColumn('product_units', 'barcode') && Schema::hasColumn('products', 'barcode')) {
            try {
                $productBarcodes = DB::table('products')->pluck('barcode', 'id');

                DB::table('product_units')
                    ->select('id', 'product_id')
                    ->orderBy('id')
                    ->get()
                    ->each(function ($unit) use ($productBarcodes): void {
                        if ($productBarcodes->has($unit->product_id)) {
                            DB::table('product_units')
                                ->where('id', $unit->id)
                                ->update(['barcode' => $productBarcodes->get($unit->product_id)]);
                        }
                    });
            } catch (\Illuminate\Database\QueryException $e) {
                // Legacy installs may not have products.barcode yet.
            }
        }

        $hasUniqueUnitCode = collect(Schema::getIndexes('product_units'))
            ->contains(function (array $index): bool {
                return ($index['unique'] ?? false)
                    && array_map('strtolower', $index['columns'] ?? []) === ['unit_code'];
            });

        if (!$hasUniqueUnitCode) {
            Schema::table('product_units', function (Blueprint $table) {
                $table->unique('unit_code');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('product_units')) {
            return;
        }

        try {
            Schema::table('product_units', function (Blueprint $table) {
                $table->unique('barcode');
            });
        } catch (Throwable $e) {
            // Keep rollback idempotent for databases with an existing index.
        }

        if (Schema::hasColumn('product_units', 'unit_code')) {
            Schema::table('product_units', function (Blueprint $table) {
                $table->dropUnique(['unit_code']);
                $table->dropColumn('unit_code');
            });
        }
    }
};
