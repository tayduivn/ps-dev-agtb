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

var myStepDefinitionsWrapper = function () {

    /**
     * Step verifies fields visible on a cached list view for the cached record.
     *
     * @example "I verify fields for *Account_A in #AccountsList:"
     */
    this.Then(/^I verify fields for (\*[A-Z](?:\w|\S)*) in (#[A-Z](?:\w|\S)*)$/,
        function (record, layout, data) {

            var listItem = listHelper.getListItem(record, layout);

            return listItem.checkFields(data.hashes()).then(errors => {

                var message = '';
                _.each(errors, function (item) {
                    message += item;
                });

                if (message) {
                    throw  new Error(message);
                }

            });

        });

    /**
     * Verify record exists on #View
     *
     * @example "I should see *Account_A in #AccountsList"
     */
    this.Then(/^I should (not )?see (\*[A-Z](?:\w|\S)*) in (#[A-Z](?:\w|\S)*)$/,
        function (not, record, layout, callback) {

            var listItem = listHelper.getListItem(record, layout);

            listItem.isVisibleView(function (value) {
                if (_.isEmpty(not) === value) {
                    callback();
                } else {
                    callback('Expected ' + (not || '') + ' to see list item (' + listItem.$() + ')');
                }
            });
        });

};

module.exports = myStepDefinitionsWrapper;
