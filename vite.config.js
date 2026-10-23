import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/css/admin-premium.css',
                'resources/css/app.css', // ← এটা add করুন
                'resources/css/ui-table.css', // shadcn-style table utilities (no preflight)
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});