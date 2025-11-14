import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/vendor-react.js',      // React vendor bundle (loads FIRST)
                // NOTE: app-react.jsx removed - Alpine bridge handles React mounting dynamically
            ],
            refresh: true,
            detectTls: true,
        }),
        tailwindcss(),
    ],

    // Configure esbuild to handle JSX directly (no React plugin needed)
    esbuild: {
        jsxFactory: 'React.createElement',
        jsxFragment: 'React.Fragment',
        // No jsxInject needed - our files already import React manually
    },

    build: {
        commonjsOptions: {
            transformMixedEsModules: true,
        },
        // Let Vite handle code-splitting naturally - no manual chunks
        // This way vendor-react.js will bundle React directly without splitting
    },

    server: {
        cors: true,
        // Increase timeout for large dependencies like React Flow
        hmr: {
            timeout: 60000, // 60 seconds
        },
    },

    resolve: {
        // Dedupe React to prevent multiple instances
        dedupe: ['react', 'react-dom'],

        alias: {
            '@': '/resources/js',
        },
    },

    optimizeDeps: {
        // Force pre-bundling of React Flow and all its dependencies
        // This ensures proper ESM/CJS conversion
        include: [
            '@xyflow/react',
            'dagre',
        ],
        esbuildOptions: {
            // Handle mixed ESM/CJS modules properly
            mainFields: ['module', 'main'],
        },
    },
});
