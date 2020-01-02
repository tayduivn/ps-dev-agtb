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

import {When, TableDefinition, seedbed} from '@sugarcrm/seedbed';
import ConsoleSettingsConfig from '../views/console-settings-view';
import {closeAlert} from './general_bdd';

/**
 * Open the Console Settings configuration drawer and navigate to specific tab
 *
 * @example When I navigate to Accounts tab in #ConsoleSettingsConfig view
 */
When(/^I navigate to (\S+) tab in (#\S+) view$/,
    async function(tabName: string, view: ConsoleSettingsConfig) {

        // Open Console Settings drawer in multiline view
        let dView = seedbed.components['AccountsRecord'].HeaderView;
        await dView.clickButton('actions');
        await dView.clickButton('edit_module_tabs_button');

        // Navigate to specific tab
        await view.navigateToTab(tabName);
    }, {waitForApp: true});


/**
 *  Set sorting order in primary or/and secondary field(s) in the Console Settings configuration drawer
 *
 *  Note: When there's no value specified in the sortBy column, the related sort order field will be cleared
 *
 *  @example
 *      When I set sort order in Accounts tab of #ConsoleSettingsConfig view:
 *       | sortOrderField | sortBy               |
 *       | primary        | Date of Next Renewal |
 *       | secondary      |                      |
 */
When(/^I set sort order in (\S+) tab of (#\S+) view:$/,
    async function(tabName: string, view: ConsoleSettingsConfig, data: TableDefinition) {

        // Open Console Settings drawer in multiline view
        await openConsoleSettingsDrawer();

        // Navigate to specific tab
        await view.navigateToTab(tabName);

        // Set sorting order
        const rows = data.rows();

        for (let i = 0; i < rows.length; i++) {
            let [sortOrder, sortingCriteria] = rows[i];

            // Only 'primary' or 'secondary' strings are supported as sort order
            if (sortOrder.toLowerCase() === 'primary' || sortOrder.toLowerCase() === 'secondary') {

                // If not empty string is supplied, go ahead and try to set specified sorting criteria to this string
                // if empty string is provided - clear sorting criteria by clicking 'x' button in the sorting order drop-down
                if( sortingCriteria || sortingCriteria.length !== 0) {
                    await view.setSortCriteria(tabName, sortingCriteria, sortOrder);
                } else {
                    await view.clearSortCriteria(tabName, sortOrder);
                }
            } else {
                throw new Error('Invalid sort order specified!');
            }
        }

        // Save changes and close confirmation alert
        await saveChanges();

    }, {waitForApp: true});


/**
 *  Set basic filter in the Console Settings configuration drawer
 *
 *  Note: only basic 'My Items' and 'My Favorites' are supported by this method
 *
 *  @example When I set the "My Items" filter in Accounts tab of #ConsoleSettingsConfig view
 */
When(/^I set the "(\D+)" filter in (\S+) tab of (#\S+) view$/,
    async function(filter: string, tabName: string, view: ConsoleSettingsConfig) {

        // Open Console Settings drawer in multiline view
        await openConsoleSettingsDrawer();

        // Navigate to specific tab
        await view.navigateToTab(tabName);

        // Set basic filter: My Items or My
        await view.setFilter(tabName, filter);

        // Save changes and close confirmation alert
        await saveChanges();

    }, {waitForApp: true});

/**
 * Open Console Settings configuration drawer
 *
 * @returns {Promise<void>}
 */
const openConsoleSettingsDrawer = async function () {
    // Open Console Settings drawer in multiline view
    let headerView = seedbed.components['AccountsRecord'].HeaderView;
    await headerView.clickButton('actions');
    await headerView.clickButton('edit_module_tabs_button');
    await seedbed.client.driver.waitForApp();
};

/**
 * Save changes made in Console Settings configuration drawer and dismiss confirmation alert
 *
 * @returns {Promise<void>}
 */
const saveChanges = async function () {
    let headerView = seedbed.components['AccountsRecord'].HeaderView;
    // Save changes and close confirmation alert
    await headerView.clickButton('save');
    await closeAlert();
};

