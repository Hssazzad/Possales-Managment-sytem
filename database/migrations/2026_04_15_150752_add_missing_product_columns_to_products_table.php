<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'brand')) {
                $table->string('brand')->nullable()->after('barcode');
            }
            if (!Schema::hasColumn('products', 'model')) {
                $table->string('model')->nullable()->after('brand');
            }
            if (!Schema::hasColumn('products', 'rack')) {
                $table->string('rack')->nullable()->after('model');
            }
            if (!Schema::hasColumn('products', 'shelf')) {
                $table->string('shelf')->nullable()->after('rack');
            }
            if (!Schema::hasColumn('products', 'pricing_type')) {
                $table->string('pricing_type')->default('single')->after('shelf');
            }
            if (!Schema::hasColumn('products', 'purchase_price')) {
                $table->decimal('purchase_price', 10, 2)->nullable()->after('pricing_type');
            }
            if (!Schema::hasColumn('products', 'mrp')) {
                $table->decimal('mrp', 10, 2)->nullable()->after('purchase_price');
            }
            if (!Schema::hasColumn('products', 'discount_percentage')) {
                $table->decimal('discount_percentage', 5, 2)->default(0)->after('mrp');
            }
            if (!Schema::hasColumn('products', 'sale_price')) {
                $table->decimal('sale_price', 10, 2)->nullable()->after('discount_percentage');
            }
            if (!Schema::hasColumn('products', 'manufacturing_date')) {
                $table->date('manufacturing_date')->nullable()->after('sale_price');
            }
            if (!Schema::hasColumn('products', 'expire_date')) {
                $table->date('expire_date')->nullable()->after('manufacturing_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
