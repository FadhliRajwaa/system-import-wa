import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        watch: {
            ignored: [
                '**/node_modules/**',
                '**/storage/**',
                '**/vendor/**',
                '**/.git/**',
                '**/public/build/**',
            ],
            usePolling: false,
        },
        hmr: {
            overlay: true,
        },
    },
    optimizeDeps: {
        exclude: ['@iconify-json/heroicons', '@iconify-json/ph'],
    },
});
