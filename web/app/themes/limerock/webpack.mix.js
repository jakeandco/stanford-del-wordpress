/*
|--------------------------------------------------------------------------
| Mix Setup
|--------------------------------------------------------------------------
|
| We use Laravel Mix to compile all assets in the theme. Let's start
| by requiring it, and adding any necessary plugins.
|
*/

let mix = require('laravel-mix');
const glob = require('glob');
require('@chiiya/laravel-mix-image-minimizer');

mix.alias({
   'src': './src',
   '@src': './src',
   '@blocks': './views/blocks'
})

/*
|--------------------------------------------------------------------------
| Compile Javascript
|--------------------------------------------------------------------------
*/
mix.js('src/js/index.js', 'dist/js/main.js')
   .sourceMaps();

mix.js('src/js/admin/index.js', 'dist/js/admin.js')
   .sourceMaps();

// Get all custom plugins JS and compile.
glob.sync('src/js/custom_plugins/*.js').forEach(file => {
   mix.js(file, file.replace('src/', 'dist/'))
   .sourceMaps();
});

// Get all block JS and compile.
// glob.sync('blocks/*/src/js/*.js').forEach(file => {
//    let blockName = file.split('/')[1];
//    mix.js(file, 'blocks/' + blockName + '/dist/js');
// });

// glob.sync('blocks/*/src/js/*.asset.php').forEach(file => {
//    let blockName = file.split('/')[1];
//    mix.copy(file, 'blocks/' + blockName + '/dist/js');
// });

/*
|--------------------------------------------------------------------------
| Compile SCSS
|--------------------------------------------------------------------------
*/
mix.sass('src/scss/index.scss', 'dist/css/main.css')
   .sourceMaps()
   .options({
      processCssUrls: false
   });

mix.sass('src/scss/_editor-base.scss', 'dist/css/editor-base.css')
   .sourceMaps()
   .options({
      processCssUrls: false
   });

// // Get all block SCSS files and compile them.
// glob.sync('blocks/*/src/scss/*.scss').forEach(file => {
//    let blockName = file.split('/')[1];
//    mix.sass(file, 'blocks/' + blockName + '/dist/css');
// });

/*
|--------------------------------------------------------------------------
| Copy Various Source Assets
|--------------------------------------------------------------------------
*/
mix.images({
   implementation: 'sharp',
   webp: true,
   patterns: [{ from: "**/!(*.svg)", to: "dist/assets/images", context: "src/assets/images" }]
});
mix.copy('src/assets/images/*.svg', 'dist/assets/images');
mix.copy('src/assets/fonts', 'dist/assets/fonts', false);

/*
|--------------------------------------------------------------------------
| BrowserSync
|--------------------------------------------------------------------------
|
| Set local proxy URL for browserSync (if desired).
|
*/
// mix.browserSync({
//    watch: true,
//    files: ['*.html'],
//    server: {
//       baseDir: "../",
//       index: "index.html",
//       directory: true
//    }
// });
