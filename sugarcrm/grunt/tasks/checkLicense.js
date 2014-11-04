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

module.exports = function(grunt) {
    grunt.registerTask('checkLicense', "Checks which files doesn't have a license on top of them", function() {
        this.async();
        var spawn = require('child_process').spawn;
        var options = grunt.config.get([this.name]);
        var file = options.licenseFile;
        var excludedExtensions = options.excludedExtensions.join('|');
        var excludedDirectories = options.excludedDirectories.join('|');
        //prepare the pattern
        var pattern = grunt.file.read(file);
        console.log(pattern);
        pattern = pattern.trim();
        pattern = pattern.replace(/\*/g, '\\*');



        pattern = pattern.replace(/\n/g, '\\s');

        pattern = pattern.replace(/\(/g, '\\(');
        pattern = pattern.replace(/\)/g, '\\)');
        console.log('pattern2: ');
        console.log(pattern);

        var cmdOptions = ['-L',
            '-r',
            '-M',
            '--exclude="(.+)\.(' + excludedExtensions + '\.(.+))"',
            '--exclude-dir="' + excludedDirectories + '"',
            '"^' + pattern + '$"',
            '.'
        ];

//      Runs the command.
        var results = spawn('pcregrep', cmdOptions);

        results.stdout.on('data', function(data) {
            console.log('stdout: ' + data);
        });

    });
};
