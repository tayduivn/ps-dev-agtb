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
var _ = require('lodash'),
    listHelper = require('../support/list-helper.js');

// turn of warnings like "Confusing use of '!'., W018"
/* jshint -W018 */

var myStepDefinitionsWrapper = function () {

    /**
     * Check whether the cached view is visible
     * Note: for *Edit and *Detail views the opened url is checked to have an id form the cached record
     *
     * @example "I should see #AccountsList view"
     */
    this.Then(/^I should (not )?see (#\S+) view$/,
        function (not, layout, callback) {
            layout.isVisibleView(function (value) {
                if (_.isBoolean(value)) {
                    !not === value ? callback() : callback.fail('Expected ' + (not || '') + 'to see "' + layout.$className + '" view(layout)');
                } else {
                    callback.fail('Error: ' + value);
                }
            });
        });

};

module.exports = myStepDefinitionsWrapper;
