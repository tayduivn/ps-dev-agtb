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

// turn of warnings like "Confusing use of '!'., W018"
/* jshint -W018 */

var myStepDefinitionsWrapper = function () {

    /**
     * Delete confirmation alert
     */
    this.When(/^I (Cancel|Confirm) confirmation alert$/, function (choice, callback) {
        var alert = seedbed.createComponent('AlertCmp', { type: 'warning' });
        alert.clickButton(choice.toLowerCase()).call(callback);
    }, true);

};

module.exports = myStepDefinitionsWrapper;
