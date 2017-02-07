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
var async = require('async');

module.exports = function() {
    this.registerHandler('BeforeFeatures', function (event, callback) {

        return seedbed.api.login({}).then(() => {
            return seedbed.api.updatePreferences({
                preferences: seedbed.config.users.default.defaultPreferences,
            });
        }).then(() => {
            callback();
        });

    });
};
