require('es6-promise').polyfill()
var elixir = require('laravel-elixir')

// Don't run gulp-notify in production
if (elixir.config.production) {
    process.env.DISABLE_NOTIFIER = true
}

elixir.config.browserSync.reloadDelay = 3000 // wait 2 seconds after a reload for laravel to find new static files

elixir(function(mix) {
    // Compile, and package all css into a single file
    mix
        .sass('app.scss')
        .copy('public/fonts', 'public/build/fonts/bootstrap')
        .webpack('main.jsx', 'public/js/main.js')
        .scripts([
            'bower_components/bootstrap-sass/assets/javascripts/bootstrap.min.js',
            'bower_components/datatables.net/js/jquery.dataTables.min.js',
            'bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js',
            'bower_components/jquery-loading/dist/jquery.loading.min.js',
            'bower_components/highcharts/highcharts.js',
        ], 'public/js/vendor.js', './')
        .copy('bower_components/html5shiv/dist/html5shiv.min.js', 'public/vendor/js/html5shiv.min.js')
        .copy('bower_components/respond/dest/respond.min.js', 'public/vendor/js/respond.min.js')
        .copy('bower_components/jquery/dist/jquery.min.js', 'public/vendor/js/jquery.min.js')
        .version(['css/app.css', 'js/main.js', 'js/tmlp-polyfill.js'])
        .browserSync({proxy: process.env.BROWSERSYNC_TARGET || 'vagrant.tmlpstats.com'})
})
