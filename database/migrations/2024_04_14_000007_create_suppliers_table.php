<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->nullable();
            $table->string('company_name')->nullable();
            $table->string('supplier_type')->nullable(); // Supplier Type
            $table->decimal('balance', 10, 2)->default(0); // Balance
            $table->decimal('due_amount', 10, 2)->default(0); // Due
            $table->decimal('credit_limit', 10, 2)->default(0); // Credit Limit
            $table->text('address')->nullable();
            $table->text('billing_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('tax_number')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('total_purchases', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('phone');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
