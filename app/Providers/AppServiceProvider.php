<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Models\ParentMenu;

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
                $menus = ParentMenu::where('status', true)
                            ->orderBy('position')
                            ->with(['submenus' => function ($q) {
                                $q->where('status', true)
                                  ->orderBy('position');
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
                        'text' => $menu->parentname,
                        'url'  => $menu->submenus->isNotEmpty() ? '#' : ($menu->mainroute ?: '#'),
                        'icon' => $menu->icon,
                    ];

                    if ($menu->submenus->count() > 0) {
                        $item['submenu'] = $menu->submenus->map(function ($child) use ($menu) {
                            return [
                                'text' => $child->submenuname,
                                'url'  => $menu->submenuUrl($child),
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
