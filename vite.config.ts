import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

import { pwa } from './resources/js/pwa/plugin.ts';
import de from './resources/js/locales/de.json' with { type: 'json' };
import en from './resources/js/locales/en.json' with { type: 'json' };

/** The TypeScript sources of the frontend, which imports reach through the alias @. */
const SRC_DIR = new URL('./resources/js', import.meta.url).pathname;

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/filament/app/theme.css', 'resources/js/main.ts'],
            refresh: true,
            assets: ['resources/images'],
        }),
        tailwindcss(),
        pwa({
            srcDir: SRC_DIR,
            locales: {
                de,
                en,
            },
            defaultLanguage: 'en',
            // The gray palette of Filament and the primary color of config/colors.php, since the service worker knows no theme
            theme: {
                light: {
                    background: '#fafafa',
                    text: '#09090b',
                    muted: '#71717a',
                    primary: '#3e8336',
                    onPrimary: '#ffffff',
                },
                dark: {
                    background: '#09090b',
                    text: '#ffffff',
                    muted: '#a1a1aa',
                    primary: '#3e8336',
                    onPrimary: '#ffffff',
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': SRC_DIR,
        },
    },
    server: {
        cors: true,
        origin: 'http://localhost:5173',
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
