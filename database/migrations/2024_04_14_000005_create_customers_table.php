<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('party_type')->nullable(); // Party Type
            $table->decimal('balance', 10, 2)->default(0); // Balance
            $table->decimal('due_amount', 10, 2)->default(0); // Due
            $table->string('email')->nullable();
            $table->decimal('credit_limit', 10, 2)->default(0); // Party Credit Limit
            $table->text('address')->nullable();
            $table->text('billing_address')->nullable(); // Billing Address
            $table->string('city')->nullable();
            $table->string('state')->nullable(); // State
            $table->string('zip_code')->nullable(); // Zip Code
            $table->string('country')->nullable();
            $table->decimal('total_purchases', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('phone');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
