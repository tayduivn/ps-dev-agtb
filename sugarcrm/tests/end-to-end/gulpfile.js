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
