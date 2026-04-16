<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('barcode')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('cost', 10, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock')->default(0);
            $table->string('category')->nullable();
            $table->string('unit')->default('pcs');
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->timestamps();
            
            $table->index(['code', 'barcode']);
            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
