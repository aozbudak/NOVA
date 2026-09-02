import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'],
            refresh: true,
            fonts: [
                bunny('Inter', {
                    weights: [400, 500, 600],
                    variable: '--font-inter',
                }),
                bunny('Source Serif 4', {
                    weights: [400, 500, 600],
                    variable: '--font-source-serif-4',
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
