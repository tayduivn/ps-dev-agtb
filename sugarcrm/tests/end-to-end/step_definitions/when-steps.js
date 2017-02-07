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
    Cukes = require('@sugarcrm/seedbed'),
    utils = Cukes.Utils,
    BaseLayout = Cukes.BaseLayout,
    listHelper = require('../support/list-helper.js'),
    stepsHelper = Cukes.StepsHelper,
    async = require('async');

// turn of warnings like "Confusing use of '!'., W018"
/* jshint -W018 */

var myStepDefinitionsWrapper = function () {

    /**
     * Select module in modules menu
     *
     * If "itemName" is visible, it means that it can be located in main menu.
     * If not - trying to open modules dropdown menu and find this module there
     *
     * @example "I choose Accounts in modules menu"
     */
    this.When(/^I choose (\w+) in modules menu$/,
        function (itemName, callback) {

            var tasks = [],
                moduleMenuCmp = seedbed.createComponent('ModuleMenuCmp');

            moduleMenuCmp.isVisible(itemName, function (isVisible) {

                if (isVisible) {

                    tasks.push(function (c) {
                        moduleMenuCmp.clickItem(itemName).call(c);
                    });
                } else {
                    tasks.push(function (c) {
                        moduleMenuCmp.showAllModules().call(c);
                    });

                    tasks.push(function (c) {
                        moduleMenuCmp.clickItem(itemName, true).call(c);
                    });
                }

                async.series(tasks, function () {
                    callback();
                });
            });
        }, true);

    /**
     * Select item from cached View
     */
    this.When(/^I select (\*[A-Z](?:\w|\S)*) in (#[A-Z]\w+)$/,
        function (record, layout, callback) {
            var listItem = listHelper.getListItem(record, layout);
            listItem.clickListItem(callback);
        }, true);

};

module.exports = myStepDefinitionsWrapper;
