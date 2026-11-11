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

        foreach (Schema::getIndexes('product_units') as $index) {
            $columns = array_map('strtolower', $index['columns'] ?? []);
            if (($index['unique'] ?? false) && in_array($columns, [['unit_code'], ['product_code']], true)) {
                Schema::table('product_units', function (Blueprint $table) use ($index): void {
                    $table->dropUnique($index['name']);
                });
            }
        }

        if (Schema::hasColumn('product_units', 'product_code')) {
            Schema::table('product_units', function (Blueprint $table): void {
                $table->string('product_code')->nullable()->change();
            });
        }

        $clear = [];
        if (Schema::hasColumn('product_units', 'unit_code')) $clear['unit_code'] = null;
        if (Schema::hasColumn('product_units', 'product_code')) $clear['product_code'] = null;
        if ($clear) DB::table('product_units')->update($clear);
    }

    public function down(): void
    {
        // Unit codes intentionally cannot be reconstructed after removal.
    }
};
