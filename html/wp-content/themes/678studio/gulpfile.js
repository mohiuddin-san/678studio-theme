const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const autoprefixer = require('gulp-autoprefixer');
const sourcemaps = require('gulp-sourcemaps');
const cleanCSS = require('gulp-clean-css');
const rename = require('gulp-rename');
const browserSync = require('browser-sync').create();
const plumber = require('gulp-plumber');
const notify = require('gulp-notify');

// Paths - テーマディレクトリを基準とした相対パス
const paths = {
  scss: {
    src: 'assets/scss/style.scss',
    watch: 'assets/scss/**/*.scss',
    dest: './'  // テーマルートディレクトリに直接出力
  },
  php: {
    watch: '**/*.php'
  },
  js: {
    watch: 'assets/js/**/*.js'
  }
};

// Error handling - improved to continue watching after errors
const handleError = (err) => {
  notify.onError({
    title: 'Gulp Error',
    message: 'Error: <%= error.message %>',
    sound: 'Beep'
  })(err);
  console.log('\n🚨 SASS Error:');
  console.log(err.toString());
  console.log('👀 Still watching for changes...\n');
  // Important: this.emit('end') prevents the task from stopping
};

// SCSS compilation task with enhanced error handling
function compileSCSS() {
  return gulp.src(paths.scss.src)
    .pipe(plumber({
      errorHandler: function(err) {
        handleError(err);
        this.emit('end'); // Prevent task from stopping
      }
    }))
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded',
      includePaths: ['assets/scss/']
    }).on('error', function(err) {
      console.log('\n🔥 SASS Compilation Error:');
      console.log('File:', err.file);
      console.log('Line:', err.line);
      console.log('Message:', err.message);
      console.log('\n🔄 Fix the error and save to try again...\n');
      this.emit('end'); // Continue watching
    }))
    .pipe(autoprefixer({
      overrideBrowserslist: ['last 2 versions'],
      cascade: false
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(browserSync.stream({ match: '**/*.css' }));
}

// SCSS build task (production)
function buildSCSS() {
  return gulp.src(paths.scss.src)
    .pipe(plumber({ errorHandler: handleError }))
    .pipe(sass({
      outputStyle: 'expanded',
      includePaths: ['assets/scss/']
    }))
    .pipe(autoprefixer({
      overrideBrowserslist: ['last 2 versions'],
      cascade: false
    }))
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(cleanCSS())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(paths.scss.dest));
}

// BrowserSync task
function initBrowserSync() {
  browserSync.init({
    proxy: 'localhost:8080', // WordPress Docker container
    port: 3000,
    open: true,
    notify: false,
    ui: false,
    // Disable caching for development
    middleware: [
      function (req, res, next) {
        res.setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        res.setHeader('Pragma', 'no-cache');
        res.setHeader('Expires', '0');
        next();
      }
    ]
  });
}

// Watch task with improved error handling
function watchFiles() {
  console.log('👀 Starting file watchers...');

  // SCSS watch with better error handling
  const scssWatcher = gulp.watch(paths.scss.watch, gulp.series(compileSCSS));

  scssWatcher.on('change', function(path) {
    console.log('📝 SCSS file changed: ' + path.replace(process.cwd(), ''));
  });

  scssWatcher.on('add', function(path) {
    console.log('➕ SCSS file added: ' + path.replace(process.cwd(), ''));
  });

  scssWatcher.on('error', function(err) {
    console.log('❌ Watch error:', err);
  });

  // PHP watch
  const phpWatcher = gulp.watch(paths.php.watch);
  phpWatcher.on('change', function(path) {
    console.log('🐘 PHP file changed: ' + path.replace(process.cwd(), ''));
    browserSync.reload();
  });

  // JS watch
  const jsWatcher = gulp.watch(paths.js.watch);
  jsWatcher.on('change', function(path) {
    console.log('⚡ JS file changed: ' + path.replace(process.cwd(), ''));
    browserSync.reload();
  });

  console.log('✅ All watchers are active. Press Ctrl+C to stop.');
}

// Create dist directories
function createDirs() {
  return gulp.src('*.*', { read: false })
    .pipe(gulp.dest(paths.scss.dest));
}

// Utility task to restart watching if it gets stuck
function restartWatch() {
  console.log('🔄 Restarting watch tasks...');
  return watchFiles();
}

// Export tasks
exports.sass = compileSCSS;
exports.build = gulp.series(createDirs, buildSCSS);
exports.watch = gulp.series(createDirs, compileSCSS, watchFiles);
exports.restart = restartWatch;
exports.default = gulp.series(createDirs, compileSCSS, gulp.parallel(initBrowserSync, watchFiles));

// Task descriptions
compileSCSS.description = 'Compile SCSS files to CSS with sourcemaps';
buildSCSS.description = 'Build minified CSS for production';
watchFiles.description = 'Watch for file changes';
initBrowserSync.description = 'Initialize BrowserSync for live reload';
restartWatch.description = 'Restart watch tasks if they get stuck';
exports.default.description = 'Default task: compile SCSS, start BrowserSync, and watch files';
exports.build.description = 'Production build: compile and minify CSS';
exports.watch.description = 'Watch files without BrowserSync';
exports.restart.description = 'Restart watch tasks (use if watch gets stuck)';
exports.sass.description = 'Compile SCSS files only';