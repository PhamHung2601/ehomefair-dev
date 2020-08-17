var gulp = require('gulp')
var less = require('gulp-less')
var connect = require('gulp-connect-php')
var browserSync = require('browser-sync')
var cleanCSS = require('gulp-clean-css')

/* Task to compile less */
gulp.task('compile-less', function () {
  gulp.src('less/style.less')
    .pipe(less())
    .pipe(gulp.dest('assets/css'))
})

/* Minify CSS */
gulp.task('minify-css', function () {
  return gulp.src('assets/css/style.css')
    .pipe(cleanCSS({compatibility: 'ie8'}))
    .pipe(gulp.dest('assets/css'))
})

gulp.task('serve', function () {
  // Serve files from the root of this project
  connect.server({}, function () {
    browserSync({
      proxy: '127.0.0.1'
    })
  })
})

/* Task to watch less changes */
gulp.task('watch-less', function () {
  gulp.watch('less/*.less', ['compile-less'])
})

gulp.task('watch-css', function () {
  gulp.watch('assets/css/*.css', ['minify-css'])
})

/* Task when running `gulp` from terminal */
gulp.task('default', ['watch-less', 'watch-css', 'serve'])
