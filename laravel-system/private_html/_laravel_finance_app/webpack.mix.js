const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.config.fileLoaderDirs.fonts = 'assets/fonts';

mix.setPublicPath('../../public_html/');

mix.js('resources/assets/js/vendor.js', 'assets/js')
    .js('resources/assets/js/app.js', 'assets/js')
    .js('resources/assets/js/admin.js', 'assets/js');


mix.sass('resources/assets/sass/app.scss', 'assets/css')
.options({
    processCssUrls: true
});
    
mix.copy('resources/assets/images', '../../public_html/assets/images', false);

if (mix.inProduction()) {
    mix.version();
}
