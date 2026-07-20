import {defineConfig} from 'vite';

export default defineConfig({
    build: {
        outDir: 'resources/dist',
        emptyOutDir: true,
        rollupOptions: {
            input: [
                'resources/css/document.css',
            ],
            output: {
                entryFileNames: 'js/[name].js',
                assetFileNames: 'css/[name].[ext]',
            },
        },
    },
});