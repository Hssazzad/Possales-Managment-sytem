<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->text('reason');
            $table->decimal('refund_amount', 10, 2);
            $table->string('refund_method');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->date('return_date');
            $table->timestamps();

            $table->index('sale_id');
            $table->index('customer_id');
            $table->index('product_id');
            $table->index('status');
            $table->index('return_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
