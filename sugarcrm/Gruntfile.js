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

var os = require('os');

module.exports = function(grunt) {
    grunt.log.error('Using Grunt is deprecated. Please use Gulp instead.\n' +
        'Refer to CONTRIBUTING.md for more information about how to use Gulp.');

    grunt.loadTasks('grunt/tasks');
    grunt.loadTasks('grunt/tasks/check-license');
    grunt.loadNpmTasks('grunt-jsduck');

    var path = grunt.option('path');
    path = path && path.replace(/\/+$/, '') + '/' || os.tmpdir();

    grunt.initConfig({
        karma: {
            options: {
                assetsDir: 'grunt/assets',
                autoWatch: false,
                browsers: ['PhantomJS'],
                configFile: 'grunt/karma.conf.js',
                singleRun: true
            },
            dev: {
                autoWatch: true,
                browsers: ['Chrome'],
                singleRun: false
            },
            coverage: {
                coverageReporter: {
                    reporters: [
                        {type: 'html', dir: path + 'karma/coverage-html'},
                        // TODO: dir should not be needed if we want the output
                        // on screen only - though if we don't specify it is
                        // created. This is probably an issue and we should
                        // report it.
                        {type: 'text', dir: path + 'karma/coverage'}
                    ]
                },
                reporters: [
                    'coverage',
                    'dots'
                ]
            },
            ci: {
                junitReporter: {
                    outputDir: path,
                    outputFile: '/karma/test-results.xml',
                    useBrowserName: false
                },
                reporters: [
                    'dots',
                    'junit'
                ]
            },
            'ci-coverage': {
                coverageReporter: {
                    reporters: [
                        {type: 'cobertura', dir: path + 'karma/coverage-xml', file: 'cobertura-coverage.xml'},
                        {type: 'html', dir: path + 'karma/coverage-html'}
                    ]
                },
                junitReporter: {
                    outputDir: path,
                    outputFile: '/karma/test-results.xml',
                    useBrowserName: false
                },
                reporters: [
                    'coverage',
                    'dots',
                    'junit'
                ]
            }
        },
        'check-license': {
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
        },
        jsduck: {
            all: {
                src: [
                    'clients/**/*.js',
                    'include/javascript/sugar7/{*,plugins/*}.js',
                    'modules/*/clients/**/*.js',
                    'sidecar/src/**/*.js',
                    'sidecar/lib/sugaraccessibility/*.js',
                    'sidecar/lib/sugaranalytics/*.js',
                    'sidecar/lib/sugarapi/sugarapi.js',
                    'sidecar/lib/sugarlogic/*.js'
                ],

                dest: 'docs',

                options: {
                    'title': 'SugarCRM Javascript Documentation',
                    'color': true,
                    'head-html': '<link rel="stylesheet" href="../styleguide/assets/css/jsduck.css" type="text/css">',
                    'builtin-classes': true,
                    'warnings': ['-all:sugarcrm/sidecar/src', '-all:sugarcrm/sidecar/lib']
                }
            }
        },
        phpunit: {
            unit: {
                cmd: 'php',
                args: [
                    'vendor/bin/phpunit',
                    '--configuration=testsunit/phpunit.xml.dist',
                    '--bootstrap=testsunit/TestBootstrap.php',
                    '--log-junit=' + path + 'unit/testunit.xml',
                    '--testdox-text=' + path + 'unit/testdox.txt',
                    'testsunit/src'
                ]
            },
            'unit-coverage': {
                cmd: 'php',
                args: [
                    'vendor/bin/phpunit',
                    '--configuration=testsunit/phpunit.xml.dist',
                    '--coverage-html=' + path + 'unit/coverage/report',
                    '--bootstrap=' + 'testsunit/TestBootstrap.php',
                    '--log-junit=' + path + 'unit/testunit.xml',
                    '--testdox-text=' + path + 'unit/testdox.txt',
                    'testsunit/src'
                ]
            },
            functional: {
                cmd: 'php',
                args: [
                    'vendor/bin/phpunit',
                    '--configuration=tests/phpunit.xml.dist',
                    '--bootstrap=tests/SugarTestHelper.php',
                    '--log-junit=' + path + 'functional/testunit.xml',
                    '--testdox-text=' + path + 'functional/testdox.txt',
                    'tests'
                ]
            },
            'functional-coverage': {
                cmd: 'php',
                args: [
                    'vendor/bin/phpunit',
                    '--configuration=tests/phpunit.xml.dist',
                    '--coverage-html=' + path + 'functional/coverage/report',
                    '--bootstrap=tests/SugarTestHelper.php',
                    '--log-junit=' + path + 'functional/testunit.xml',
                    '--testdox-text=' + path + 'functional/testdox.txt',
                    'tests'
                ]
            }
        }
    });
};
