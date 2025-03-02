import {defineConfig} from 'vite';
import laravel, {refreshPaths} from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/filament/app/theme.css'
            ],
            refresh: [
                ...refreshPaths,
                'resources/**',
                'app/Filament/**',
            ],
        }),
    ],
});
