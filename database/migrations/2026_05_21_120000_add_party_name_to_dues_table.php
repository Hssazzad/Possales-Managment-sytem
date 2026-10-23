<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dues', function (Blueprint $table) {
            if (!Schema::hasColumn('dues', 'party_name')) {
                $table->string('party_name')->nullable()->after('type');
                $table->index('party_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dues', function (Blueprint $table) {
            $table->dropIndex(['party_name']);
            $table->dropColumn('party_name');
        });
    }
};
