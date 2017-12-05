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
const gulp = require('gulp');
const path = require('path');
const tslint = require('gulp-tslint');
const gulpts = require('gulp-typescript');
const gutil = require('gulp-util');
const ts = require('typescript');
const os = require('os');

gulp.task('tslint', () => {

    let src = ['./**/*.ts', '!node_modules/**/*.ts'];

    return gulp.src(src)
        .pipe(
            tslint({
                formatter: 'stylish',
                configuration: "./tslint.json",
            })
        )
        .pipe(tslint.report());
});

gulp.task('ts', () => {

    let project = gulpts.createProject('tsconfig.json', {
        typescript: ts,
    });

    const tsResult = project.src().pipe(project())
        .on('error', function (error) {

            let colors = gutil.colors;

            console.log(colors.red(error));

            process.exit(1);
        });

    return tsResult.pipe(gulp.dest(path.resolve(os.tmpdir(), 'cukests')));

});
