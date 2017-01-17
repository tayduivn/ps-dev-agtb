var gulp = require('gulp');
var jshint = require('gulp-jshint');

gulp.task('validate-sugar', function() {
    return gulp.src('**/*.js')
        .pipe(jshint())
        .pipe(jshint.reporter());
});

gulp.task('validate-seedbed', function() {
    return gulp.src('seedbed/**/*.js')
        .pipe(jshint())
        .pipe(jshint.reporter());
});

gulp.task('code-style', ['validate-sugar', 'validate-seedbed']);
