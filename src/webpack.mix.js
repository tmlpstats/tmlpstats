let mix = require('laravel-mix')


mix
    .react('resources/assets/js/main.jsx', 'public/build/js')
    .extract(['es6-promise', 'react', 'react-router', 'immutable', 'moment'])
    .sass('resources/assets/sass/app.scss', 'public/build/css')
    .scripts([
        'node_modules/bootstrap-sass/assets/javascripts/bootstrap.min.js',
        'bower_components/datatables.net/js/jquery.dataTables.min.js',
        'bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js',
        'bower_components/jquery-loading/dist/jquery.loading.min.js',
        'bower_components/highcharts/highcharts.js',
    ], 'public/build/js/classic-vendor.js')
    .copy([
        'bower_components/html5shiv/dist/html5shiv.min.js',
        'node_modules/core-js/client/shim.min.js',
        'bower_components/respond/dest/respond.min.js',
        'bower_components/jquery/dist/jquery.min.js',
    ], 'public/vendor/js/')

if (process.env.IN_LOCALDEV_WATCH) {
    mix.browserSync({
        port: 8030,
        proxy: 'localhost',
        open: false  // Hides the warning about can't open browser
    })
} else {
    mix.browserSync(process.env.BROWSERSYNC_TARGET || 'localhost:8080')
}

if (mix.config.inProduction || process.env.IN_LOCALDEV) {
    mix.version()
    mix.disableNotifications()
}
