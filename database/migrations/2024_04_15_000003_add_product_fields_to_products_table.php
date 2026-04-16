<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('barcode');
            $table->string('model')->nullable()->after('brand');
            $table->string('rack')->nullable()->after('model');
            $table->string('shelf')->nullable()->after('rack');
            $table->string('pricing_type')->default('single')->after('shelf'); // single or batch
            $table->decimal('purchase_price', 10, 2)->nullable()->after('pricing_type');
            $table->decimal('mrp', 10, 2)->nullable()->after('purchase_price');
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('mrp');
            $table->decimal('sale_price', 10, 2)->nullable()->after('discount_percentage');
            $table->date('manufacturing_date')->nullable()->after('sale_price');
            $table->date('expire_date')->nullable()->after('manufacturing_date');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['brand', 'model', 'rack', 'shelf', 'pricing_type', 'purchase_price', 'mrp', 'discount_percentage', 'sale_price', 'manufacturing_date', 'expire_date']);
        });
    }
};
