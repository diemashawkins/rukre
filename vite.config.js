import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { fontsource } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                fontsource('Syne', { variable: '--rk-font-display', weights: [700, 800] }),
                fontsource('Plus Jakarta Sans', { variable: '--rk-font-body', weights: [400, 500, 600, 700] }),
                fontsource('Space Grotesk', { variable: '--rk-font-label', weights: [500, 600, 700] }),
                fontsource('Literata', { variable: '--rk-font-serif', weights: [400, 600], styles: ['normal', 'italic'], preload: false }),
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
