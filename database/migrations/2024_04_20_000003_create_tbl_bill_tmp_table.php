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
        if (Schema::hasTable('tbl_bill_tmp')) {
            return;
        }

        Schema::create('tbl_bill_tmp', function (Blueprint $table) {
            $table->id();
            $table->string('main_code', 50);
            $table->string('sub_code', 50);
            $table->string('name', 100);
            $table->decimal('amount', 10, 2);
            $table->string('patient_code', 50);
            $table->string('bill_no', 50)->nullable()->index();
            $table->tinyInteger('status')->default(0)->comment('0=temp, 1=final');

            $table->foreign('main_code')
                ->references('code')
                ->on('main_categories')
                ->onDelete('cascade');

            $table->foreign('sub_code')
                ->references('code')
                ->on('sub_categories')
                ->onDelete('cascade');

            // Index for better query performance (bill_no already indexed on column)
            $table->index(['patient_code', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_bill_tmp');
    }
};
