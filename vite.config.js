import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import vuetify, { transformAssetUrls } from 'vite-plugin-vuetify';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.ts',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls,
            },
        }),
        vuetify({
            autoImport: true,
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            'ziggy-js': '/vendor/tightenco/ziggy',
        },
    },
});
