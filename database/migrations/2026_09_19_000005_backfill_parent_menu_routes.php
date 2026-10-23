<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus') || ! Schema::hasTable('parentmenu')) {
            return;
        }

        DB::table('menus')->whereNull('parent_id')->orderBy('id')->each(function ($menu) {
            if (! $menu->url || $menu->url === '#') {
                return;
            }

            DB::table('parentmenu')
                ->where('parentcode', (int) ($menu->code ?: $menu->id * 10))
                ->where(function ($query) {
                    $query->whereNull('mainroute')->orWhere('mainroute', '');
                })
                ->update(['mainroute' => trim($menu->url, '/')]);
        });
    }

    public function down(): void
    {
        // Original route values remain available in the legacy menus table.
    }
};
