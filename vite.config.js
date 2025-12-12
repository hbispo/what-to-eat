import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: 'whattoeat.lan',
        https: true, // Vite will generate its own self-signed certificate
        hmr: {
            host: 'whattoeat.lan',
            protocol: 'wss',
        },
    },
});
