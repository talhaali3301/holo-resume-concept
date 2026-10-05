import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import { bunny, fontsource } from 'laravel-vite-plugin/fonts'
import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
            fonts: [
                bunny('Instrument Serif', { weights: [400], styles: ['normal', 'italic'], optimizedFallbacks: false }),
                fontsource('Geist', { package: '@fontsource/geist-sans', weights: [400, 500, 600], optimizedFallbacks: false }),
                fontsource('Geist Mono', { package: '@fontsource/geist-mono', weights: [400, 500], optimizedFallbacks: false }),
            ],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
})
