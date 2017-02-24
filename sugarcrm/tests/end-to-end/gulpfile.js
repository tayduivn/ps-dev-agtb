/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
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
