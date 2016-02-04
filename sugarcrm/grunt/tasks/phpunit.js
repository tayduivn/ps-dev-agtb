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

var _ = require('lodash');

module.exports = function(grunt) {

    grunt.registerMultiTask('phpunit', function() {
        // FIXME: when paralelization support is introduced the code bellow
        // should no longer be needed
        var testsuite = grunt.option('testsuite') || '';
        if (testsuite.length) {

            // discard any parameters that aren't prepended with a `-`, usually
            // supplied directory, which needs to be discarded when
            // `--testsuite` is supplied.
            this.data.args = _.filter(this.data.args, function(arg) {
                return arg.match(/^-/);
            });

            // update file names for `--log-junit` and `--testdox-text` based
            // on supplied `--testsuite` name.
            _.each(this.data.args, function(arg, k) {
               if (arg.match(/^--log-junit/) || arg.match(/^--testdox-text/)) {
                   var ext = arg.lastIndexOf('.');
                   if (ext === -1) {
                       this.data.args[k] = arg + testsuite;
                   } else {
                       this.data.args[k] = arg.substr(0, ext) + '-' + testsuite + arg.substr(ext);
                   }
               }
            }, this);

            this.data.args.push('--testsuite=' + testsuite);
        }

        grunt.util.spawn({
            cmd: this.data.cmd,
            args: this.data.args,
            opts: {stdio: 'inherit'}
        }, this.async());
    });
};
