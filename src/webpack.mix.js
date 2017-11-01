let mix = require('laravel-mix')
let webpack = require('webpack')

mix.webpackConfig({
    plugins: [
        // yucky hack, but works
        new webpack.ContextReplacementPlugin(/graphql-language-service-interface[\/\\]dist/, /\.js$/),
    ]
})

mix
    .react('resources/assets/js/main.jsx', 'public/build/js')
    .extract([
        'es6-promise', 'react', 'react-router', 'immutable', 'moment',
        'fetch-ponyfill', 'prop-types', 'react-redux', 'redux-responsive', 'redux-thunk',
        'redux', 'reselect'
    ])
    .sass('resources/assets/sass/app.scss', 'public/build/css')
    .scripts([
        'node_modules/bootstrap-sass/assets/javascripts/bootstrap.min.js',
        'node_modules/datatables.net/js/jquery.dataTables.js',
        'node_modules/datatables.net-bs/js/dataTables.bootstrap.js',
        'node_modules/jquery-easy-loading/dist/jquery.loading.min.js',
        'node_modules/highcharts/highcharts.js',
    ], 'public/build/js/classic-vendor.js')
    .copy(['node_modules/graphiql/graphiql.css'], 'public/vendor/css/')
    .copy([
        'node_modules/html5shiv/dist/html5shiv.min.js',
        'node_modules/core-js/client/shim.min.js',
        'node_modules/respond.js/dest/respond.min.js',
        'node_modules/jquery/dist/jquery.min.js',
    ], 'public/vendor/js/')

if (process.env.IN_LOCALDEV_WATCH) {
    mix.browserSync({
        port: 8030,
        proxy: 'localhost',
        open: false  // Hides the warning about can't open browser
    })
} else {
    mix.browserSync(process.env.BROWSERSYNC_TARGET || 'localhost:8000')
}

if (mix.inProduction() || process.env.IN_LOCALDEV) {
    mix.version()
    mix.disableNotifications()
}

if (!mix.inProduction()) {
    mix.sourceMaps()
}
