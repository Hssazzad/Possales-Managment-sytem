<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->string('controller_name')->nullable()->after('icon');
            $table->string('main_route')->nullable()->unique()->after('controller_name');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropUnique(['main_route']);
            $table->dropColumn(['controller_name', 'main_route']);
        });
    }
};
