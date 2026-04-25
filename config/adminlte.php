<?php

return [

    'title' => 'Possales',
    'title_prefix' => '',
    'title_postfix' => '',

    'use_ico_only' => false,
    'use_full_favicon' => false,

    'google_fonts' => [
        'allowed' => true,
    ],

    'logo' => '<b>Possales</b>',
    'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Possales',

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Possales',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Possales Preloader',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => true,
    'usermenu_desc' => true,
    'usermenu_profile_url' => false,

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    'classes_body'             => 'sidebar-mini layout-fixed sidebar-dark-primary',
    'classes_brand'            => 'bg-white border-bottom',
    'classes_brand_text'       => 'text-dark font-weight-bold',
    'classes_content_wrapper'  => 'bg-light',
    'classes_content_header'   => '',
    'classes_content'          => '',
    'classes_sidebar'          => 'sidebar-light-primary elevation-1',
    'classes_sidebar_nav'      => 'nav-child-indent',
    'classes_topnav'           => 'navbar-white navbar-light border-bottom',
    'classes_topnav_nav'       => 'navbar-expand',
    'classes_topnav_container' => 'container-fluid',

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    'use_route_url' => false,
    'dashboard_url' => 'dashboard',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => 'profile',
    'disable_darkmode_routes' => false,

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    'extra_assets' => [
        'css' => [],
        'js'  => [],
    ],

    'menu' => [
        [
            'type'         => 'navbar-search',
            'text'         => 'search',
            'topnav_right' => true,
        ],
        [
            'type'         => 'fullscreen-widget',
            'topnav_right' => true,
        ],
        [
            'type' => 'sidebar-menu-search',
            'text' => 'search',
        ],
        [
            'text'   => 'dashboard',
            'url'    => 'dashboard',
            'icon'   => 'fas fa-fw fa-tachometer-alt',
            'active' => ['dashboard'],
        ],
        [
            'text'    => 'sales',
            'icon'    => 'fas fa-fw fa-shopping-cart',
            'submenu' => [
                ['text' => 'new_sale',  'url' => '#', 'icon' => 'fas fa-fw fa-plus'],
                ['text' => 'sale_list', 'url' => '#', 'icon' => 'fas fa-fw fa-list'],
            ],
        ],
        [
            'text'    => 'purchases',
            'icon'    => 'fas fa-fw fa-truck',
            'submenu' => [
                ['text' => 'new_purchase',  'url' => '#', 'icon' => 'fas fa-fw fa-plus'],
                ['text' => 'purchase_list', 'url' => '#', 'icon' => 'fas fa-fw fa-list'],
            ],
        ],
        [
            'text' => 'products',
            'url'  => '#',
            'icon' => 'fas fa-fw fa-box',
        ],
        [
            'text' => 'stock_list',
            'url'  => '#',
            'icon' => 'fas fa-fw fa-warehouse',
        ],
        [
            'text' => 'customers',
            'url'  => '#',
            'icon' => 'fas fa-fw fa-users',
        ],
        [
            'text' => 'suppliers',
            'url'  => '#',
            'icon' => 'fas fa-fw fa-industry',
        ],
        [
            'text' => 'incomes',
            'url'  => '#',
            'icon' => 'fas fa-fw fa-money-bill-wave',
        ],
        [
            'text' => 'expenses',
            'url'  => '#',
            'icon' => 'fas fa-fw fa-file-invoice',
        ],
        [
            'text' => 'tax_setting',
            'url'  => '#',
            'icon' => 'fas fa-fw fa-percent',
        ],
        [
            'text' => 'due_list',
            'url'  => '#',
            'icon' => 'fas fa-fw fa-clock',
        ],
        ['header' => 'ACCOUNT'],
        [
            'text' => 'My Profile',
            'url'  => 'profile',
            'icon' => 'fas fa-fw fa-user-circle',
        ],
    ],

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files'  => [
                ['type' => 'js',  'asset' => false, 'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'],
                ['type' => 'js',  'asset' => false, 'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js'],
                ['type' => 'css', 'asset' => false, 'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css'],
            ],
        ],
        'Chartjs' => [
            'active' => true,
            'files'  => [
                ['type' => 'js', 'asset' => false, 'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js'],
            ],
        ],
    ],

    'iframe' => [
        'default_tab' => ['url' => null, 'title' => null],
        'buttons' => [
            'close'           => true,
            'close_all'       => true,
            'close_all_other' => true,
            'scroll_left'     => true,
            'scroll_right'    => true,
            'fullscreen'      => true,
        ],
        'options' => [
            'loading_screen'    => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items'  => true,
        ],
    ],

    'livewire' => false,
];
