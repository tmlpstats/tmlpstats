var elixir = require('laravel-elixir');

// Allow us to use the .jsx extension for React files
elixir.config.js.browserify.options.extensions = ['.jsx', '.js']

elixir(function(mix) {
    mix.less('app.less')
      .browserify(['main.jsx'])
      .version(['css/tmlpstats.css', 'css/app.css', 'js/bundle.js', 'js/api.js', 'js/tmlpstats.js'])
      .browserSync({proxy: 'vagrant.tmlpstats.com'});
});
