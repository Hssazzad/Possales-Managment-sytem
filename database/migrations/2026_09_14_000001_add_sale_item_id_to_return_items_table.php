<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('return_items', function (Blueprint $table) {
            if (!Schema::hasColumn('return_items', 'sale_item_id')) {
                $table->unsignedBigInteger('sale_item_id')->nullable()->after('sale_id');
                $table->index('sale_item_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('return_items', function (Blueprint $table) {
            if (Schema::hasColumn('return_items', 'sale_item_id')) {
                $table->dropIndex(['sale_item_id']);
                $table->dropColumn('sale_item_id');
            }
        });
    }
};
