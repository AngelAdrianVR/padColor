import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';


export default defineConfig({
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
    // Comentar esta sección si se desea usar el servidor de desarrollo de Vite con HTTP.
    server: {
        detectTls: 'padcolor.test',
        hmr: {
            host: 'padcolor.test',
            protocol: 'wss',
        },
        // Vite recarga la página al detectar cambios en archivos .blade.php durante el desarrollo (npm run dev).
        // Esta regla evita el refresco automático al subir archivos al portal de clientes.
        watch: {
            ignored: ['**/resources/views/external/**']
        }
    },
});
