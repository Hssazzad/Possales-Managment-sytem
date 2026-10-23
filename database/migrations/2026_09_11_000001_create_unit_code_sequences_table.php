<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('unit_code_sequences')) {
            Schema::create('unit_code_sequences', function (Blueprint $table) {
                $table->string('sequence_key')->primary();
                $table->unsignedBigInteger('current_value')->default(0);
                $table->timestamps();
            });
        }

        $highestSerial = 0;

        if (Schema::hasTable('product_units') && Schema::hasColumn('product_units', 'unit_code')) {
            DB::table('product_units')
                ->whereNotNull('unit_code')
                ->pluck('unit_code')
                ->each(function ($unitCode) use (&$highestSerial): void {
                    if (preg_match('/(\d+)$/', (string) $unitCode, $matches)) {
                        $highestSerial = max($highestSerial, (int) $matches[1]);
                    }
                });
        }

        DB::table('unit_code_sequences')->updateOrInsert(
            ['sequence_key' => 'product_unit'],
            [
                'current_value' => $highestSerial,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_code_sequences');
    }
};
