import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';


export default defineConfig({
    // Vite recarga la página al detectar cambios en archivos .blade.php durante el desarrollo (npm run dev).
    // Esta regla evita el refresco automático al subir archivos al portal de clientes.
    server: {
        watch: {
            ignored: ['**/resources/views/external/**']
        }
    },
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
