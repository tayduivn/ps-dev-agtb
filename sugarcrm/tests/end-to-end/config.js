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
var path = require('path');
const _ = require('lodash');

var temp_folder = path.resolve(__dirname, './tmp');

var featuresPath = './features';
for (var i in process.argv) {
    if (process.argv[i] === '--features') {
        featuresPath = process.argv[Number(i) + 1];
        break;
    }
}

var config = {

    temp_folder: temp_folder,

    //Absolute path to project folder
    rootPath: path.resolve(__dirname, './'),
    inactiveProcessTimeout: 60000,

    cucumberArguments: [
        {
            value: path.resolve(__dirname, featuresPath)
        },
        {
            name: '-r',
            value: path.resolve(__dirname, './step_definitions')
        },
    ],

    proxy: {
        cacheEnabled: true,

        // Prepare for proxy plugins refactoring
        pages: {
            index: {
                url: ['/index.html', '/', ''],
                load: function(scripts) {
                    scripts = scripts.concat([
                        {
                            id: 'sugar:clientConfig',
                            content: function(config, clientConfig) {
                                var content = 'SUGAR.App.config = seedbed.utils.deepExtend(SUGAR.App.config, JSON.parse(\'' + JSON.stringify(clientConfig) + '\')); \r\n'
                                    + 'seedbed.sugarOverrideConfig = JSON.parse(\'' + JSON.stringify(clientConfig) + '\'); \r\n';
                                return content;
                            },
                            insertAfter: '</script>'
                        },
                        {
                            id: 'sugar:init.js',
                            path: path.join(__dirname, 'client', 'init.js'),
                            insertAfter: '</script>'
                        },
                        {
                            id: 'sugar:client.css',
                            path: path.join(__dirname, 'client', 'client.css'),
                            style: true,
                            insertAfter: '</script>'
                        }
                    ]);

                    return scripts;
                }
            },

            bwc_index: {
                url: function(url) {
                    return url.indexOf('index.php?') !== -1;
                },

                load: function(scripts) {
                    scripts = _.filter(scripts, script => {
                        return (
                            script.id === 'seedbed:logger.js' ||
                            script.id === 'seedbed:utils.js'
                        );
                    });

                    scripts.unshift({
                        id: 'sugar:bwc-init.js',
                        path: path.join(__dirname, 'client', 'bwc-init.js'),
                        insertAfter: '</script>',
                    });

                    return scripts;
                },
            }
        }
    },

    devices: {
        desktop_chrome: {
            experimental: false,
            desiredCapabilities: {
                browserName: 'chrome',
                loggingPrefs: {'browser': 'ALL'},
                chromeOptions: {
                    // Use this option to setup a custom Chrome binary location f.e.
                    // binary : '/Applications/Google Chrome 2.app/Contents/MacOS/Google Chrome',
                    args: [
			'--no-sandbox',
			'--user-data-dir=' + temp_folder,
                        '--disable-web-security',
                        '--disable-extensions',
                        '--window-size=1460,1080'
                    ]
                }
            }
        }
    },

    // Seedbed client configuration that is passed to init scripts by proxy
    timeouts: {
        maxTimeoutCatchValue: 1300,
        maxWait: 120000,
    },

    responsePayloads: {},
    metadataPayloads: {},
    testSchemes: require('./support/test-schemes'),
    workingDir : __dirname,

    // Define SugarCRM Api platform (could be base|mobile|portal)
    sugarPlatform : 'base',

    output: {
        resultsFailures: path.resolve(__dirname, './'),
        screenshots: path.resolve(__dirname, './screenshots'),
    },

    require: [
        path.resolve(__dirname, 'seedbed')
    ],
    clientScripts: require('./client/client-scripts.js'),
    client: {
        devicePixelRatio: 1,
    },
    selenium: {
        debug: false,

        // is local version or not
        local: false,

        // selenium-standalone install options
        options: {
            // check for more recent versions of selenium here:
            // http://selenium-release.storage.googleapis.com/index.html
            version: '3.0.1',
            baseURL: 'http://selenium-release.storage.googleapis.com',
            drivers: {
                chrome: {
                    // check for more recent versions of chrome driver here:
                    // http://chromedriver.storage.googleapis.com/index.html
                    version: '2.38',
                    arch: process.arch,
                    baseURL: 'http://chromedriver.storage.googleapis.com'
                }
            }
        }
    },
    users: {
        admin: {
            login: 'admin',
            password: 'asdf',
            defaultPreferences: {
                timezone: 'America/Los_Angeles',
                timepref: 'h:ia',
                datepref: 'm/d/Y',
                default_locale_name_format: 's f l',
                ut: true,
                max_tabs: 7,
                reminder_time: 1800,
            },
        },
        default: {
            login: 'admin',
            password: 'asdf',
            defaultPreferences: {
                timezone: 'America/Los_Angeles',
                timepref: 'h:ia',
                datepref: 'm/d/Y',
                default_locale_name_format: 's f l',
                ut: true,
                max_tabs: 7,
                reminder_time: 1800,
            },
        },
        defaultPreferences: {
            timezone: 'America/Los_Angeles',
            timepref: 'h:ia',
            datepref: 'm/d/Y',
            default_locale_name_format: 's f l',
            ut: true,
            max_tabs: 7,
            reminder_time: 1800,
            show_wizard: false,
        }
    },

    log: {
        level: 'debug',
        requests: true,
        responses: true,
    },

    licenses: {
        sugar: process.env.SUGAR_KEY,
    },

    apiUrl: '/rest',
    apiVersion: 'v10',

};

module.exports = {config};
