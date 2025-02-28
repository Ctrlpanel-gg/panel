import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import path from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "themes/default/css/app.css",
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
        },
    },
    optimizeDeps: {
        include: [
            '@tiptap/core',
            '@tiptap/starter-kit',
            '@tiptap/extension-highlight',
            '@tiptap/extension-underline',
            '@tiptap/extension-link',
            '@tiptap/extension-text-align',
            '@tiptap/extension-image',
            '@tiptap/extension-youtube',
            '@tiptap/extension-text-style',
            '@tiptap/extension-font-family',
            '@tiptap/extension-color',
            '@tiptap/extension-bold'
        ]
    },
    build: {
        commonjsOptions: {
            include: [/node_modules/]
        }
    }
});
