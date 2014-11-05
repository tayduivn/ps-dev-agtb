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

var os = require('os');

module.exports = function(grunt) {
    grunt.loadTasks('grunt/tasks');
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
                    outputFile: path + 'karma/test-results.xml'
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
                    outputFile: path + 'karma/test-results.xml'
                },
                reporters: [
                    'coverage',
                    'dots',
                    'junit'
                ]
            }
        },
        checkLicense: {
            excludedExtensions: [
                'json',
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
                'ttf'
            ],
            excludedFiles: [
            // Array of paths to exclude. Not regex patterns, only plain strings.
            // Could be a filename, a part of the path, or the full path.
                'sidecar/lib/',
                'include/javascript/yui3/',
                'styleguide/less/twitter-bootstrap'
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
            licenseFile: 'grunt/assets/licenseHeader'
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
        }
    });
};
