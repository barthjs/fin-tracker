import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/filament/app/theme.css', 'resources/js/main.ts'],
            refresh: true,
            assets: ['resources/images'],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        origin: 'http://localhost:5173',
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
