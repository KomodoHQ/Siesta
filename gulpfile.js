var gulp = require('gulp');
var notify  = require('gulp-notify');
var phpunit = require('gulp-phpunit');


var files = {
  "php": ['./tests/*.php','src/Quickjob/**/**/*.php']
};

gulp.task('watch',function() {
  gulp.watch(files.php, ['phpunit']);
});

gulp.task('phpunit', function() {
  var options = {debug: false, notify: true};
  gulp.src(['./tests/*.php'])
  .pipe(phpunit('phpunit', options))
  .on('error', notify.onError({
    title: "Failed Tests!",
    message: "Error(s) occurred during testing..."
  }));
});
