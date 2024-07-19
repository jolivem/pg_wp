const esbuild = require('esbuild');
const { sassPlugin } = require('esbuild-sass-plugin');

// Determine the build mode (development or production)
const isProd = process.env.NODE_ENV === 'production';
//isProd = true;

// Common settings for both dev and prod
//entryPoints: ['public/js/glp-public.js', 'src/styles.scss'],
const commonConfig = {
  bundle: true,
  plugins: [sassPlugin()],
};

// Production-specific settings
const prodConfig = {
  minify: true,
  sourcemap: false,
};

// Development-specific settings
const devConfig = {
  minify: false,
  sourcemap: true,
};

const buildCSS = {
  ...commonConfig,
  entryPoints: ['public/css/glp-public.scss'],
  outdir: 'public/dist',
  ...(isProd ? prodConfig : devConfig),
};

//entryPoints: ['public/js/glp-public.js'],
const buildJS = {
  ...commonConfig,
  entryPoints: [],
  outdir: 'dist/js',
  ...(isProd ? prodConfig : devConfig),
};

Promise.all([
    esbuild.build(buildJS),
    esbuild.build(buildCSS)
]).then(() => {
  console.log('Build complete');
}).catch(() => process.exit(1));
