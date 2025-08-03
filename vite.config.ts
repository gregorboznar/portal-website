export default defineConfig({
  base: '/',
  plugins: [
    laravel({
      input: ['resources/js/app.ts'],
      ssr: 'resources/js/ssr.ts',
      refresh: true,
    }),
    tailwindcss(),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    svgLoader(),
  ],
  server: {
    host: '0.0.0.0',
    port: 5173,
    hmr: {
      host: 'portal.glab.si', // change from localhost to your domain for remote HMR if needed
    },
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
      'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
    },
  },
});
