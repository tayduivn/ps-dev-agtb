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
const execa = require('execa');
const path = require('path');

gulp.task('ts', function() {

    let tsProcess = execa('./node_modules/.bin/gulp',
        [
            'ts', '--color',
            '--include', `${path.resolve(__dirname, './**/*.ts')}`,
            '--exclude', `${path.resolve(__dirname, 'node_modules/**')}`,
        ],
        {
            stdio: 'inherit',
            cwd: path.resolve(__dirname, './node_modules/@sugarcrm/seedbed'),
        });

    return tsProcess;

});
