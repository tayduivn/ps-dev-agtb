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
import {chooseModule, closeAlert, closeWarning, toggleSpecifiedRecords} from './general_bdd';
import MergeLayout from '../layouts/merge-layout';
import ListView from '../views/list-view';

/**
 *      Merge (or Cancel merge of) selected records without any changes
 *
 *      @example
 *      When I perform merge of Leads [*L_1, *L_2, *L_3] records with the following changes:
 *
 */
When(/^I (perform|cancel) merge of (\w+) (\[(?:\*\w+)(?:,\s*(?:\*\w+))*\]) records$/,
    async function (action: string, module: string, inputIDs: string) {

        await chooseModule(module);
        const listView = await seedbed.components[`${module}List`].ListView;

        // Toggle selected records
        await toggleSpecifiedRecords(inputIDs, listView);

        // Select 'Merge' action
        await listView.selectAction('Merge');
        await this.driver.waitForApp();

        // Proceed with selected action
        await executeSelectedAction(inputIDs, listView, action);

    }, {waitForApp: true}
);

/**
 *      Merge (or Cancel merge of) selected records after updating specified fields values immediately prior to the merge
 *
 *      @example
 *      When I perform merge of Leads [*L_1, *L_2, *L_3] records with the following changes:
 *          | fieldName  | value                   |
 *          | first_name | Jessica                 |
 *          | last_name  | Cho                     |
 *
 */
When(/^I (perform|cancel) merge of (\w+) (\[(?:\*\w+)(?:,\s*(?:\*\w+))*\]) records with the following changes:$/,
    async function (action: string, module: string, inputIDs: string, table: TableDefinition) {

        await chooseModule(module);
        const listView = await seedbed.components[`${module}List`].ListView;
        const mergeDrawer = await seedbed.components['MergeDrawer'];

        // Toggle selected records
        await toggleSpecifiedRecords(inputIDs, listView);

        // Select 'Merge' action
        await listView.selectAction('Merge');
        await this.driver.waitForApp();

        // Enter specified changes before merge
        await populateMergeData(mergeDrawer, module, table);

        // Proceed with selected action
        await executeSelectedAction(inputIDs, listView, action);

    }, {waitForApp: true}
);

/**
 *      Perform (or Cancel) records merge with marking specified fields from secondary record to be primary immediately prior to the merge
 *
 *      @example
 *      When I perform merge of Leads [*L_1, *L_2, *L_3] records with the following changes:
 *          | fieldName  |
 *          | first_name |
 *          | last_name  |
 *          | title      |
 */
When(/^I (perform|cancel) merge of (\w+) (\[(?:\*\w+)(?:,\s*(?:\*\w+))*\]) records with the following updates:$/,
    async function (action: string, module, inputIDs: string, table: TableDefinition) {

        await chooseModule(module);
        const listView = await seedbed.components[`${module}List`].ListView;
        const mergeDrawer = await seedbed.components['MergeDrawer'];

        // Toggle selected records
        await toggleSpecifiedRecords(inputIDs, listView);

        // Select 'Merge' action
        await listView.selectAction('Merge');
        await this.driver.waitForApp();

        // Enter specified changes before merge
        await changePrimaryField(mergeDrawer, module, table);

        // Proceed with selected action
        await executeSelectedAction(inputIDs, listView, action);

    }, {waitForApp: true}
);

/**
 *      Attempt to perform records merge using invalid number of records or invalid record combination
 *
 *      @example
 *      When I perform out of range merge of Leads [*L_1, *L_2, *L_3] records
 */
When(/^I perform out of range merge of (\w+) (\[(?:\*\w+)(?:,\s*(?:\*\w+))*\]) records$/,
    async function (module: string, inputIDs: string) {

        await chooseModule(module);
        const listView = await seedbed.components[`${module}List`].ListView;

        // Toggle selected records
        await toggleSpecifiedRecords(inputIDs, listView);

        // Select 'Merge' action
        await listView.selectAction('Merge');
        await this.driver.waitForApp();

    }, {waitForApp: true}
);

/**
 *  Remove primary or FIRST secondary record prior to proceeding with merge operation
 *
 *  @examples
 *  When I remove primary record before merge of Leads [*L_1, *L_2, *L_3, *L_4] records
 */
When(/^I remove (primary|secondary) record before merge of (\w+) (\[(?:\*\w+)(?:,\s*(?:\*\w+))*\]) records$/,
    async function (action: string, module: string, inputIDs: string) {

        await chooseModule(module);
        const listView = await seedbed.components[`${module}List`].ListView;

        // Toggle selected records
        await toggleSpecifiedRecords(inputIDs, listView);

        // Select 'Merge' action
        await listView.selectAction('Merge');
        await this.driver.waitForApp();

        await removeRecordFromMerge(action);
        await this.driver.waitForApp();

        // Proceed with selected action
        await executeSelectedAction(inputIDs, listView, 'perform');

    }, {waitForApp: true}
);

/**
 *
 * Change primary record's value prior to proceeding with merge operation
 *
 * @param {MergeLayout} mergeDrawer
 * @param {string} module
 * @param {TableDefinition} table
 * @returns {Promise<void>}
 */
const populateMergeData = async function (mergeDrawer: MergeLayout, module: string, table: TableDefinition) {
    const rows = table.rows();

    // Update specified field's values
    for (let i = 0; i < rows.length; i++) {

        let row = rows[i];
        let fieldName = row[0];
        let fieldValue = row[1];

        // Update value for selected fields before merge is saved
        await mergeDrawer.setNewPrimaryFieldValue(fieldName, fieldValue);
    }
};

/**
 *  Set Primary field values prior to proceeding with the merge
 *
 * @param {MergeLayout} mergeDrawer
 * @param module
 * @param {TableDefinition} table
 * @returns {Promise<void>}
 */
const changePrimaryField = async function (mergeDrawer: MergeLayout, module, table: TableDefinition) {
    const rows = table.rows();

    // Populate mass update with data from the table
    for (let i = 0; i < rows.length; i++) {

        let row = rows[i];
        let fieldName = row[0];

        // Change the primary field for selected field
        await mergeDrawer.changePrimaryField(fieldName);
    }
};

/**
 * Proceed with the merge and dismiss all messages displayed during and right after the merge process completes
 *
 * @returns {Promise<void>}
 */
const performMerge = async function () {

    // Proceed with the merge
    await seedbed.components['MergeDrawer'].HeaderView.clickButton('Save');
    await closeWarning('Confirm'); // Confirm merge
    await seedbed.client.driver.waitForApp();
    await closeAlert(); // Close first "Success: Saved" alert
    await closeAlert(); // Close second "Merge Summary" alert
    await seedbed.client.driver.waitForApp();
};

/**
 * Cancel merge process and un-check all records in the list view previously selected for merging
 *
 * @param {string} inputIDs
 * @param {ListView} listView
 * @returns {Promise<void>}
 */
const cancelMerge = async function (inputIDs: string, listView: ListView) {

    // Cancel the merge process
    await seedbed.components['MergeDrawer'].HeaderView.clickButton('Cancel');
    seedbed.client.driver.waitForApp();
    // Uncheck all previously checked records
    await toggleSpecifiedRecords(inputIDs, listView);
};

/**
 * Proceed with or cancel merge process
 *
 * @param {string} inputIDs
 * @param {ListView} listView
 * @param {string} button
 * @returns {Promise<void>}
 */
const executeSelectedAction = async function (inputIDs: string, listView: ListView, button: string) {
        switch (button) {

            // Proceed with the merge
            case 'perform':
                await performMerge();
                break;

            // Cancel the merge and uncheck all selected records
            case 'cancel':
                await cancelMerge(inputIDs, listView);
                break;

            default:
                throw new Error('Invalid button name');
    }
};

/**
 *
 * Remove record from merge in the Merge drawer
 *
 * Note: This method only applicable for the case when 3, 4 or 5 (Max) records are selected to be merged
 *
 * @param action
 * @returns {Promise<void>}
 */
const removeRecordFromMerge = async function (action) {

    // Remove record from merge
    const mergeDrawer = await seedbed.components['MergeDrawer'];
    await mergeDrawer.removeRecord(action);
    await closeWarning('Confirm');
    seedbed.client.driver.waitForApp();
};
