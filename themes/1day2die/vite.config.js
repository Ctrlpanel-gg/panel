import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import path from "path";



export default defineConfig({
    plugins: [
        laravel({
            input: [
                "themes/1day2die/sass/app.scss",
                "themes/1day2die/js/app.js"
            ],
            buildDirectory: "1day2die",
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
            '@': '/themes/1day2die/js',
            '~bootstrap': path.resolve('node_modules/bootstrap'),
        }
    },
    
});
