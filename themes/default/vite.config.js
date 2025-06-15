import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import path from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "themes/default/css/app.css",
                "themes/default/css/select2.css",
                "themes/default/sass/app.scss",
                "themes/default/js/app.js"
            ],
            buildDirectory: "build",
        }),
        {
            name: "blade",
            handleHotUpdate({ file, server }) {
                if (file.endsWith(".blade.php")) {
                    server.ws.send({
                        type: "full-reload",
                        path: "*",
                    });
                }
            },
        },
    ],
    resolve: {
        alias: {
            "@": "/themes/default/js",
            "~bootstrap": path.resolve("node_modules/bootstrap"),
            "~select2-theme": path.resolve("node_modules/select2-tailwindcss-theme/dist"),
        },
    },
    optimizeDeps: {
        include: []
    },
    build: {
        commonjsOptions: {
            include: [/node_modules/]
        },
        minify: 'terser',
        sourcemap: false,
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['axios', 'bootstrap', 'jquery']
                }
            }
        }
    }
});
