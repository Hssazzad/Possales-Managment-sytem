<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_units') || !Schema::hasColumn('product_units', 'barcode')) {
            return;
        }

        foreach (Schema::getIndexes('product_units') as $index) {
            $columns = array_map('strtolower', $index['columns'] ?? []);
            if (($index['unique'] ?? false) && $columns === ['barcode']) {
                Schema::table('product_units', function (Blueprint $table) use ($index): void {
                    $table->dropIndex($index['name']);
                });
            }
        }
    }

    public function down(): void
    {
        // Duplicate product barcodes are intentional and must remain supported.
    }
};
