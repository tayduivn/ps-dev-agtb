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
'use strict';

import {TableDefinition} from 'cucumber';
import {When, seedbed} from '@sugarcrm/seedbed';
import {chooseModule, closeAlert, toggleSpecifiedRecords, provideRecordViewInput, recordViewHeaderButtonClicks} from './general_bdd';

/**
 *      Add records of specified module (or Cancel adding) to new target list while in the list view
 *
 *      @example
 *          When I add Leads [*L_1, *L_2, *L_3] to new target list with the following values:
 *          | *     | name            |
 *          | PRL_1 | New Target List |
 *
 */
When(/^I (add|cancel adding) (\w+) (\[(?:\*\w+)(?:,\s*(?:\*\w+))*\]) to new target list with the following values:$/,
    async function (action: string, module: string, inputIDs: string, table: TableDefinition) {

        const listView = await seedbed.components[`${module}List`].ListView;
        let drawer_view = await seedbed.components[`ProspectListsDrawer`];

        // Choose module
        await chooseModule(module);

        // Toggle selected records
        await toggleSpecifiedRecords(inputIDs, listView);

        // Select 'Add To Target List' action
        await listView.selectAction('Add To Target List');
        await this.driver.waitForApp();

        // Create New Prospect List
        await seedbed.components[`${module}List`].FilterView.performTargetListAction('create');
        await this.driver.waitForApp();

        // Provide record input
        // TODO: currently only Header changes are allowed.
        // TODO: AT-238 is filed to address this limitation
        await provideRecordViewInput(drawer_view.HeaderView, table);

        // Save
        await recordViewHeaderButtonClicks('Save', drawer_view);
        await closeAlert();


        // Proceed with selected action
        switch (action) {
            case 'add':

                // Add records to newly created list
                await seedbed.components[`${module}List`].FilterView.performTargetListAction('update');
                await this.driver.waitForApp();

                // Dismiss confirmation alert
                await closeAlert();
                await this.driver.waitForApp();
                break;

            case 'cancel adding':

                // Click Cancel
                await seedbed.components[`${module}List`].FilterView.performTargetListAction('cancel');
                seedbed.client.driver.waitForApp();

                // Uncheck all previously checked records
                await toggleSpecifiedRecords(inputIDs, listView);
                break;

            default:
                throw new Error('Invalid action name specified');
        }

    }, {waitForApp: true}
);


/**
 *      Add records of specified module (or Cancel adding) to existed target list while in the list view
 *
 *      @example
 *          When I add Leads [*L_1, *L_2, *L_3] to new "List Name" target list
 *
 */
When(/^I (add|cancel adding) (\w+) (\[(?:\*\w+)(?:,\s*(?:\*\w+))*\]) to \*(\w+) target list$/,
    async function (action: string, module: string, inputIDs: string, name: string) {

        const listView = await seedbed.components[`${module}List`].ListView;
        let record = await seedbed.cachedRecords.get(name);

        // Choose module
        await chooseModule(module);

        // Toggle selected records
        await toggleSpecifiedRecords(inputIDs, listView);

        // Select 'Add To Target List' action
        await listView.selectAction('Add To Target List');
        await this.driver.waitForApp();

        // Select existing Target List
        await seedbed.components[`${module}List`].FilterView.selectExistingTargetList(record.input.get('name'));
        await this.driver.waitForApp();

        // Proceed with selected action
        switch (action) {
            case 'add':

                // Add records to newly created list
                await seedbed.components[`${module}List`].FilterView.performTargetListAction('update');
                await this.driver.waitForApp();

                // Dismiss confirmation alert
                await closeAlert();
                await this.driver.waitForApp();
                break;

            case 'cancel adding':

                // Click Cancel
                await seedbed.components[`${module}List`].FilterView.performTargetListAction('cancel');
                seedbed.client.driver.waitForApp();

                // Uncheck all previously checked records
                await toggleSpecifiedRecords(inputIDs, listView);
                break;

            default:
                throw new Error('Invalid action name specified');
        }

    }, {waitForApp: true}
);
