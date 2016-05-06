/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

var _ = require('lodash');
var commander = require('commander');
var fs = require('fs');
var gulp = require('gulp');
var gutil = require('gulp-util');
var karma = require('karma').server;
var os = require('os');

gulp.task('karma', function(done) {
    // get command-line arguments (only relevant for karma tests)
    commander
        .option('-d, --dev', 'Set Karma options for debugging')
        .option('--path <path>', 'Set code coverage path (implies --coverage)')
        .option('--browsers <list>',
                'Comma-separated list of browsers to run tests with',
                function(val) { return val.split(','); })
        .parse(process.argv);

    var coverageRoot = os.tmpdir();
    if (commander.path) {
        commander.coverage = true;
        coverageRoot = commander.path;
    }

    // retrieve test data
    var karmaAssets = _.flatten([
        eval(fs.readFileSync('grunt/assets/base-files.js', 'utf8')),
        eval(fs.readFileSync('grunt/assets/default-tests.js', 'utf8'))
    ], true);

    // set up Karma
    var karmaOptions = {
        files: karmaAssets,
        autoWatch: false,
        browsers: commander.browsers || ['PhantomJS'],
        configFile: __dirname + '/grunt/karma.conf.js',
        singleRun: true,
        reporters: ['dots']
    };

    if (commander.dev) {
        karmaOptions.browsers = commander.browsers || ['Chrome'];
        karmaOptions.autoWatch = true;
        karmaOptions.singleRun = false;
    }

    return karma.start(karmaOptions, function(exitStatus) {
        // Karma's return status is not compatible with gulp's streams
        // See: http://stackoverflow.com/questions/26614738/issue-running-karma-task-from-gulp
        // or: https://github.com/gulpjs/gulp/issues/587 for more information
        done(exitStatus ? 'There are failing unit tests' : undefined);
    });
});

gulp.task('check-license', function() {

    var options = {
        excludedExtensions: [
            'json',
            'swf',
            'log',
            // image files
            'gif',
            'jpeg',
            'jpg',
            'png',
            'ico',
            // special system files
            'DS_Store',
            // Doc files
            'md',
            'txt',
            // vector files
            'svg',
            'svgz',
            // font files
            'eot',
            'ttf',
            'woff',
            'otf',
            // stylesheets
            'less',
            'css'
        ],
        // Array of directory patterns (PCRE regex).
        // Only works with the name, not its path.
        excludedDirectories: [
            'node_modules',
            'vendor',
            'tests',
            // sugarcharts should be ignored
            'SugarCharts'
        ],
        licenseFile: 'LICENSE',
        // Add paths you want to exclude in the whiteList file.
        whiteList: 'grunt/assets/check-license/license-white-list.json'
    };

    var exec = require('child_process').exec;

    var licenseFile = options.licenseFile;
    var whiteList = options.whiteList;
    var excludedExtensions = options.excludedExtensions.join('|');
    var excludedDirectories = options.excludedDirectories.join('|');

    //Prepares excluded files.
    eval('var excludedFiles = ' + fs.readFileSync(whiteList) + '.excludedFiles', 'utf8');
    excludedFiles = excludedFiles.join('\\n');

    var pattern = fs.readFileSync(licenseFile).toString();
    pattern = pattern.trim();

    //Add '*' in front of each line.
    pattern = pattern.replace(/\n/g, '\n \*');
    //Add comment token at the beginning and the end of the text.
    pattern = pattern.replace(/^/, '/\*\n \*');
    pattern = pattern.replace(/$/, '\n \*/');
    //Put spaces after '*'.
    pattern = pattern.replace(/\*(?=\w)/g, '\* ');

    // Prepares the PCRE pattern.
    pattern = pattern.replace(/\*/g, '\\*');
    pattern = pattern.replace(/\n/g, '\\s');
    pattern = pattern.replace(/\(/g, '\\(');
    pattern = pattern.replace(/\)/g, '\\)');

    var cmdOptions = [
        '--buffer-size=10M',
        '-M',
        // The output will be a list of files that don't match the pattern.
        '-L',
        // Recursive mode.
        '-r',
        // Ignores case.
        '-i',
        // Excluded directories.
        '--exclude-dir="' + excludedDirectories + '"',
        // Excluded extensions.
        '--exclude="((.*)\.(' + excludedExtensions + '))"',
        // Pattern to match in each file.
        '"^' + pattern + '$"',
        // Directory where the command is executed.
        '.'
    ];

    var command = 'pcregrep ' + cmdOptions.join(' ') + '| grep -v -F "$( printf \'' + excludedFiles + '\' )"';

    //Runs the command.
    exec(command, {maxBuffer: 2000 * 1024}, function(error, stdout, stderr) {
        if (stderr.length != 0) {
            gutil.log(gutil.colors.red('Exec error:'), stderr);
        } else if (stdout.length != 0) {
            gutil.log(gutil.colors.red('Invalid license headers found in:'), stdout);
        } else {
            gutil.log('All files have the exact license specified in `sugarcrm/LICENSE`');
        }
    });
});
