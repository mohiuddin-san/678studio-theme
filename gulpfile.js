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
    dest: 'dist/css/',
    themeRoot: './'  // テーマルートディレクトリ
  },
  php: {
    watch: '**/*.php'
  },
  js: {
    watch: 'assets/js/**/*.js'
  }
};

// Error handling
const handleError = (err) => {
  notify.onError({
    title: 'Gulp Error',
    message: 'Error: <%= error.message %>',
    sound: 'Beep'
  })(err);
  console.log(err.toString());
};

// SCSS compilation task
function compileSCSS() {
  return gulp.src(paths.scss.src)
    .pipe(plumber({ errorHandler: handleError }))
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded',
      includePaths: ['assets/scss/']
    }))
    .pipe(autoprefixer({
      overrideBrowserslist: ['last 2 versions'],
      cascade: false
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(gulp.dest(paths.scss.themeRoot)) // Copy to theme root for WordPress
    .pipe(browserSync.stream());
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
    .pipe(gulp.dest(paths.scss.themeRoot)) // Copy expanded version to theme root for WordPress
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

// Watch task
function watchFiles() {
  gulp.watch(paths.scss.watch, compileSCSS);
  gulp.watch(paths.php.watch).on('change', browserSync.reload);
  gulp.watch(paths.js.watch).on('change', browserSync.reload);
}

// Create dist directories
function createDirs() {
  return gulp.src('*.*', { read: false })
    .pipe(gulp.dest(paths.scss.dest));
}

// Export tasks
exports.sass = compileSCSS;
exports.build = gulp.series(createDirs, buildSCSS);
exports.watch = gulp.series(createDirs, compileSCSS, watchFiles);
exports.default = gulp.series(createDirs, compileSCSS, gulp.parallel(initBrowserSync, watchFiles));

// Task descriptions
compileSCSS.description = 'Compile SCSS files to CSS with sourcemaps';
buildSCSS.description = 'Build minified CSS for production';
watchFiles.description = 'Watch for file changes';
initBrowserSync.description = 'Initialize BrowserSync for live reload';
exports.default.description = 'Default task: compile SCSS, start BrowserSync, and watch files';
exports.build.description = 'Production build: compile and minify CSS';
exports.watch.description = 'Watch files without BrowserSync';
exports.sass.description = 'Compile SCSS files only';