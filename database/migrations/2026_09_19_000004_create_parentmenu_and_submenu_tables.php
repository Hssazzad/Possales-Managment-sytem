<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parentmenu', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parentcode')->unique();
            $table->string('parentname');
            $table->string('controllername')->nullable();
            $table->string('mainroute')->nullable();
            $table->string('icon')->default('fas fa-fw fa-folder');
            $table->unsignedInteger('position')->default(1);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('submenu', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menuparentcode');
            $table->unsignedBigInteger('submenucode');
            $table->string('submenuname');
            $table->string('method');
            $table->string('icon')->default('far fa-circle');
            $table->unsignedInteger('position')->default(1);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->unique(['menuparentcode', 'submenucode']);
        });

        if (Schema::hasTable('menus')) {
            $parents = DB::table('menus')->whereNull('parent_id')->orderBy('sort_order')->get();
            foreach ($parents as $parent) {
                $code = (int) ($parent->code ?: $parent->id * 10);
                DB::table('parentmenu')->insert([
                    'parentcode' => $code,
                    'parentname' => $parent->text,
                    'controllername' => $parent->controller_name,
                    'mainroute' => $parent->main_route,
                    'icon' => $parent->icon,
                    'position' => max(1, (int) $parent->sort_order),
                    'status' => (bool) $parent->is_active,
                    'created_at' => now(), 'updated_at' => now(),
                ]);

                foreach (DB::table('menus')->where('parent_id', $parent->id)->orderBy('sort_order')->get() as $child) {
                    DB::table('submenu')->insert([
                        'menuparentcode' => $code,
                        'submenucode' => (int) ($child->code ?: $code . str_pad((string) $child->id, 2, '0', STR_PAD_LEFT)),
                        'submenuname' => $child->text,
                        'method' => $child->url,
                        'icon' => $child->icon,
                        'position' => max(1, (int) $child->sort_order),
                        'status' => (bool) $child->is_active,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('submenu');
        Schema::dropIfExists('parentmenu');
    }
};
