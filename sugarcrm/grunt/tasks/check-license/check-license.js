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

module.exports = function(grunt) {
    grunt.registerTask('check-license', 'Outputs files that do not have the exact sugar license specified in `sugarcrm/LICENSE`', function() {
        this.async();
        var exec = require('child_process').exec;
        var options = grunt.config.get([this.name]);
        var licenseFile = options.licenseFile;
        var whiteList = options.whiteList;
        var excludedExtensions = options.excludedExtensions.join('|');
        var excludedDirectories = options.excludedDirectories.join('|');

        //Prepares excluded files.
        var excludedFiles = grunt.file.readJSON(whiteList).excludedFiles;
        excludedFiles = excludedFiles.join('\\n');

        var pattern = grunt.file.read(licenseFile);
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
            '--buffer-size=100k',
            '-M',
            // The output will be file names of files that doesnt match the pattern.
            '-L',
            // Recursive mode.
            '-r',
            // Ignores case.
            '-i',
            //Excluded directories.
            '--exclude-dir="' + excludedDirectories + '"',
            // Excluded extensions.
            '--exclude="((.*)\.(' + excludedExtensions + '))"',
            //Pattern to match in each file.
            '"^' + pattern + '$"',
            //Directory where the command is executed.
            '.'
        ];
        var command = 'pcregrep ' + cmdOptions.join(' ') + '| grep -v -F "$( printf \'' + excludedFiles + '\' )"';

        //Runs the command.
        exec(command, {maxBuffer: 2000 * 1024}, function(error, stdout, stderr) {

            if (error && error.code === 1) {
                grunt.log.ok('All files have the exact license specified in `sugarcrm/LICENSE`');
            } else {
                grunt.log.subhead('Invalid license headers found in:');
                grunt.log.error(stdout);
            }
        });
    });
};
