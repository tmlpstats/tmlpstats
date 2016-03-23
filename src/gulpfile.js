var elixir = require('laravel-elixir');

var source = require('vinyl-source-stream');
var gulp = require('gulp');
var gutil = require('gulp-util');
var babelify = require('babelify');
var browserify = require('browserify');
var watchify = require('watchify');
var notify = require('gulp-notify');

// Production output processors
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var buffer = require('vinyl-buffer');

var browserSync = require('browser-sync');
var reload = browserSync.reload;

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.less('app.less');
});

var config = {
  proxyServer: "vagrant.tmlpstats.com",
  jsSource: './resources/assets/jsx/',
  jsDest: './public/js/',
  jsDestFile: 'main.js',
  jsDestFileProd: 'main.min.js',
  production: !!gutil.env.production
};

gulp.task('browser-sync', function() {
  // if (!config.production) {
    browserSync({
      proxy: config.proxyServer,
      logConnections: false,
      reloadOnRestart: false,
      notify: false
    });
  // }
});

gulp.task('scripts', function() {
  return buildScript(config.jsDestFile, false); // this will run once because we set watch to false
});

// run 'scripts' task first, then watch for future changes
gulp.task('default', ['scripts','browser-sync'], function() {
  return buildScript(config.jsDestFile, true); // browserify watch for JS changes
});

function handleErrors() {
  var args = Array.prototype.slice.call(arguments);
  notify.onError({
    title: 'Compile Error',
    message: '<%= error.message %>'
  }).apply(this, args);
  this.emit('end'); // Keep gulp from hanging on this task
}

function buildScript(file, watch) {
  var props = {
    entries: [config.jsSource + file],
    debug : true,
    transform: [
      babelify.configure({
        presets: ["react", "es2015"]
      })
    ]
  };

  // watchify() if watch requested, otherwise run browserify() once
  var bundler = watch ? watchify(browserify(props)) : browserify(props);

  function rebundle() {
    var stream = bundler.bundle(),
        prod = config.production;

    return stream
      .on('error', handleErrors)
      .pipe(source(file))
      .pipe(gulp.dest(config.jsDest))
      .pipe(prod ? buffer() : gutil.noop())
      .pipe(prod ? uglify() : gutil.noop())
      .pipe(prod ? rename(config.jsDestFileProd) : gutil.noop())
      .pipe(prod ? gulp.dest(config.jsDest) : gutil.noop())
      .pipe(reload({stream:true}))
  }

  // listen for an update and run rebundle
  bundler.on('update', function() {
    rebundle();
    gutil.log('Rebundle...');
  });

  // run it once the first time buildScript is called
  return rebundle();
}
