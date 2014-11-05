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
        var exec = require('child_process').exec;
//        var spawn = require('child_process').spawn;
        var options = grunt.config.get([this.name]);
        var licenseFile = options.licenseFile;
        var excludedExtensions = options.excludedExtensions.join('|');
        var excludedDirectories = options.excludedDirectories.join('|');
        var excludedFiles = options.excludedFiles.join('|');

        //prepare the pattern
        var pattern = grunt.file.read(licenseFile);
        pattern = pattern.trim();
        pattern = pattern.replace(/\*/g, '\\*');
        pattern = pattern.replace(/\n/g, '\\s');
        pattern = pattern.replace(/\(/g, '\\(');
        pattern = pattern.replace(/\)/g, '\\)');

        var cmdOptions = [
            '--buffer-size=100k',
            '-M',
            // The output will be file names of files that doesnt match the pattern.
            '-L',
            // Recursive mode
            '-r',
            // ignore case
            '-i',
//            '--exclude="' + excludedFiles + '"',
//            //Excluded directories
            '--exclude-dir="' + excludedDirectories + '"',
            // Excluded extensions
            '--exclude="((.*)\.(' + excludedExtensions + '))"',
            // Excluded files
            //Pattern to match in each file.
            '"^' + pattern + '$"',
            //Directory where the command is executed.
            '.'
        ];

        var command = 'pcregrep ' + cmdOptions.join(' ');

//      Runs the command.
        var results = exec(command , {maxBuffer: 2000*1024}, function (error, stdout, stderr) {

            grunt.log.debug(error);

            if (error && error.code === 1) {
//                no results found !!! all good - see....
            } else {
                throw new Error('Bad license headers found at least in: \n' + stdout);
            }

//
//            console.log('stdout: ' + stdout);
//            console.log(error);
//            console.log('stderr: ' + stderr);
//
//            if (stderr) {
//            console.log('stderr: ' + stderr);
//            }
//            if (error !== null) {
//                console.log('exec error: ' + error);
//            }
        });


// Using 'spawn' instead of exec
//        var results = spawn('pcregrep', cmdOptions);
//
//        results.stdout.on('data', function(data) {
//            console.log('stdout: ' + data);
//        });

//        return 'pcregrep -L -r -M ' +
//            '--exclude="(.+)\.(json|gif|png|min\.(.+))" ' +
//            '--exclude-dir="node_modules|vendor" ' +
//            '"^' + pattern + '$" .';


    });
};
