import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/caf.css',
                'resources/js/app.js',
                'resources/css/admin.css',
                'resources/js/admin-app.js',
                'resources/css/admin-login.css',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                    optimizedFallbacks: false,
                }),
                bunny('Comfortaa', {
                    weights: [400, 500, 700],
                    optimizedFallbacks: false,
                }),
                bunny('Montserrat', {
                    weights: [600, 700, 800],
                    optimizedFallbacks: false,
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
