<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_units') || !Schema::hasColumn('product_units', 'product_variation_id')) {
            return;
        }

        Schema::table('product_units', function (Blueprint $table) {
            $table->dropForeign(['product_variation_id']);
            $table->foreignId('product_variation_id')->nullable()->change();
            $table->foreign('product_variation_id')
                ->references('id')
                ->on('product_variations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('product_units') || !Schema::hasColumn('product_units', 'product_variation_id')) {
            return;
        }

        Schema::table('product_units', function (Blueprint $table) {
            $table->dropForeign(['product_variation_id']);
            $table->foreignId('product_variation_id')->nullable(false)->change();
            $table->foreign('product_variation_id')
                ->references('id')
                ->on('product_variations')
                ->cascadeOnDelete();
        });
    }
};