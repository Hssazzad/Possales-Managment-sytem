<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->string('code', 20)->nullable()->unique()->after('text');
        });

        // Backfill existing rows:
        // Main Menu -> 10, 20, 30 ... (by sort_order)
        // Submenu   -> parent code + 2-digit sequence, e.g. 1001, 1002
        $mains = DB::table('menus')->whereNull('parent_id')
            ->orderBy('sort_order')->orderBy('id')->get();

        $i = 1;
        foreach ($mains as $main) {
            $code = (string) ($i * 10);
            while (DB::table('menus')->where('code', $code)->exists()) {
                $i++;
                $code = (string) ($i * 10);
            }
            DB::table('menus')->where('id', $main->id)->update(['code' => $code]);

            $children = DB::table('menus')->where('parent_id', $main->id)
                ->orderBy('sort_order')->orderBy('id')->get();

            $j = 1;
            foreach ($children as $child) {
                $childCode = $code . str_pad((string) $j, 2, '0', STR_PAD_LEFT);
                while (DB::table('menus')->where('code', $childCode)->exists()) {
                    $j++;
                    $childCode = $code . str_pad((string) $j, 2, '0', STR_PAD_LEFT);
                }
                DB::table('menus')->where('id', $child->id)->update(['code' => $childCode]);
                $j++;
            }

            $i++;
        }
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};
