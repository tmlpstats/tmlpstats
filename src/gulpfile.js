var elixir = require('laravel-elixir');

// Allow us to use the .jsx extension for React files
elixir.config.js.browserify.options.extensions = ['.jsx', '.js'];

elixir(function(mix) {
    // Compile, and package all css into a single file
    mix.styles([
            'bower_components/bootstrap/dist/css/bootstrap.min.css',
            'bower_components/datatables/media/css/dataTables.bootstrap.min.css',
            'bower_components/jquery-loading/dist/jquery.loading.min.css',
            'bower_components/font-awesome/css/font-awesome.min.css',
            'public/css/tmlpstats.css'
        ], 'public/css/main.css', './')
       .copy( 'public/fonts', 'public/build/fonts' );

    // Compile, and package all js into a single file. Copy a few files we need to include separately
    mix.browserify(['main.jsx'])
       .scripts([
            'bower_components/bootstrap/dist/js/bootstrap.min.js',
            'bower_components/datatables.net/js/jquery.dataTables.min.js',
            'bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js',
            'bower_components/jquery-loading/dist/jquery.loading.min.js',
            'bower_components/jquery-stickytabs/jquery.stickytabs.js',
            'bower_components/moment/min/moment-with-locales.min.js',
            'bower_components/highcharts/highcharts.js',
            'bower_components/jstz/jstz.min.js',
            'public/js/api.js',
            'public/js/bundle.js',
            'public/js/tmlpstats.js'
        ], 'public/js/main.js', './')
        .copy('bower_components/html5shiv/dist/html5shiv.min.js', 'public/vendor/js/html5shiv.min.js')
        .copy('bower_components/respond/dest/respond.min.js', 'public/vendor/js/respond.min.js')
        .copy('bower_components/jquery/dist/jquery.min.js', 'public/vendor/js/jquery.min.js');

    // Setup versioning
    mix.version(['css/main.css', 'js/main.js'])
       .browserSync({proxy: 'vagrant.tmlpstats.com'})
});
