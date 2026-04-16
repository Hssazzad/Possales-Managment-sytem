<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        view()->composer('*', function ($view) {
            if (Auth::check()) {

                // ── Dynamic menu from database ──
                $menus = Menu::whereNull('parent_id')
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->with(['children' => function ($q) {
                                $q->where('is_active', true)
                                  ->orderBy('sort_order');
                            }])
                            ->get();

                $menuItems = [];

                // ── Fixed top items ──
                $menuItems[] = [
                    'type'         => 'navbar-search',
                    'text'         => 'search',
                    'topnav_right' => true,
                ];
                $menuItems[] = [
                    'type'         => 'fullscreen-widget',
                    'topnav_right' => true,
                ];
                $menuItems[] = [
                    'type' => 'sidebar-menu-search',
                    'text' => 'search',
                ];

                // ── Database থেকে menu build ──
                foreach ($menus as $menu) {
                    $item = [
                        'text' => $menu->text,
                        'url'  => $menu->url,
                        'icon' => $menu->icon,
                    ];

                    if ($menu->children->count() > 0) {
                        $item['submenu'] = $menu->children->map(function ($child) {
                            return [
                                'text' => $child->text,
                                'url'  => $child->url,
                                'icon' => $child->icon,
                            ];
                        })->toArray();
                    }

                    $menuItems[] = $item;
                }

                config(['adminlte.menu' => $menuItems]);
            }
        });
    }
}
