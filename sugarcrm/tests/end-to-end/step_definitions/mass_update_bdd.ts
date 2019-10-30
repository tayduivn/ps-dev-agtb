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

import {Hashmap} from '@sugarcrm/seedbed';
import {TableDefinition} from 'cucumber';
import {When, seedbed} from '@sugarcrm/seedbed';
import {chooseModule, closeAlert, toggleRecord, parseInputArray, closeWarning, toggleSpecifiedRecords} from './general_bdd';
import MassupdateView from '../views/massupdate-view';
import * as moment from 'moment';

/**
 *  Perform (or cancel) mass update of all records present on the list view
 *
 *  @example
 *  When I perform mass update of all Quotes with the following values:
 *      | fieldName                  | value           |
 *      | quote_stage                | Closed Accepted |
 */
When(/^I (perform|cancel) mass update of all (\w+) with the following values:$/,
    async function (button: string, module, table: TableDefinition) {
        await chooseModule(module);
        const listView = await seedbed.components[`${module}List`].ListView;
        const massUpdateView = await seedbed.components[`${module}List`].MassUpdateView;

        // Toggle all records
        await listView.toggleAll();

        // Select mass update action
        await listView.selectAction('Mass Update');

        // Populate mass update with data from the table
        await populateMassUpdate(massUpdateView, module, table);

        switch (button) {
            // Perform mass update and close alert
            case 'perform':
                await buttonClickMassUpdate('update', massUpdateView);
                await closeAlert();
                break;
            // Cancel mass update and uncheck all selected records
            case 'cancel':
                await buttonClickMassUpdate('cancel', massUpdateView);
                // uncheck all selected records
                await listView.toggleAll();
                break;
            default:
                throw new Error('Invalid button name');
        }
    }, {waitForApp: true}
);

/**
 * Populate mass update information based on specified values
 *
 * @param massUpdateView
 * @param module
 * @param {TableDefinition} table
 * @returns {Promise<void>}
 */
const populateMassUpdate = async function (massUpdateView, module, table: TableDefinition) {
    const rows = table.rows();

    // Populate mass update with data from the table
    for (let i = 0; i < rows.length; i++) {

        // Add new Mass Update row after first one is filled
        if (i !== 0) {
            await  massUpdateView.addRow(i + 1);
        }

        let row = rows[i];
        let fieldName = row[0];
        let fieldValue = row[1];

        if (fieldValue === 'today') {
            fieldValue = moment().format('MM/DD/YYYY');
        }

        // Get Field Label
        let argumentsArray = [];
        argumentsArray.push(fieldName, module);
        let fieldLabel = await seedbed.client.driver.execSync('getLabelByFieldName', argumentsArray);

        // Set value for field selector ('parent field')
        await massUpdateView.setFieldValue(fieldLabel.value, i + 2);

        // Set new value for the selected field ('child field')
        let inputData = new Hashmap();
        inputData.push(fieldName, fieldValue);
        await massUpdateView.setFieldsValue(inputData);

        // Get Field object
        let fieldObject = await seedbed.client.driver.execSync('getFieldDef', argumentsArray);

        // Dismiss confirmation alert for fields of 'populate_list' type
        if (fieldObject.value.populate_list && (fieldName.indexOf('account_name') !== -1 )) {
            await closeWarning('Confirm');
        }
    }
};

/**
 * Click mass update (or cancel) button
 *
 * @param {string} btnName
 * @param {MassupdateView} view
 * @returns {Promise<any>}
 */
const buttonClickMassUpdate = async function (btnName: string, view: MassupdateView) {
    return view.performAction(btnName.toLowerCase());
};

/**
 * Perform (or cancel) mass update of selected records
 *
 *  @example
 *  When I perform mass update of Quotes [*Q_1, *Q_2, *Q_4] with the following values:
 *      | fieldName                  | value           |
 *      | quote_stage                | Closed Accepted |
 *      | date_quote_expected_closed | 12/31/2020      |
 *
 */
When(/^I (perform|cancel) mass update of (\w+) (\[(?:\*\w+)(?:,\s*(?:\*\w+))*\]) with the following values:$/,
    async function(button: string, module, inputIDs: string, table: TableDefinition) {

        await chooseModule(module);
        const listView = await seedbed.components[`${module}List`].ListView;
        const massUpdateView = await seedbed.components[`${module}List`].MassUpdateView;

        // Toggle selected records
        await toggleSpecifiedRecords(inputIDs, listView);

        // Select mass update action
        await listView.selectAction('Mass Update');

        // Populate mass update with data from the table
        await populateMassUpdate(massUpdateView, module, table);

        switch (button) {
            // Perform mass update and close alert
            case 'perform':
                await buttonClickMassUpdate('update', massUpdateView);
                await closeAlert();
                break;
            // Cancel mass update and uncheck all selected records
            case 'cancel':
                await buttonClickMassUpdate('cancel', massUpdateView);
                // uncheck all selected records
                await listView.toggleAll();
                break;
            default:
                throw new Error('Invalid button name');
        }
    }, {waitForApp: true}
);

/**
 *  Recalculate values for all records in the List view
 *
 *  @examples
 *  When I recalculate all values in Quotes
 */
When(/^I recalculate all values in (\w+)$/,
    async function (module) {

        // Navigate to specified module
        await chooseModule(module);
        await seedbed.client.driver.waitForApp();
        const listView = await seedbed.components[`${module}List`].ListView;

        // Toggle all records
        await listView.toggleAll();
        this.driver.waitForApp();

        // Select Recalculate Values
        await listView.selectAction('Recalculate Values');
        this.driver.waitForApp();

        // Close Alert
        await closeAlert();
        this.driver.waitForApp();

    }, {waitForApp: true}
);

/**
 *  Recalculate values for specific records in the List view
 *
 *  @examples
 *  When I recalculate [*Q_1, *Q_2, *Q_4] values in Quotes
 */
When(/^I recalculate (\[(?:\*\w+)(?:,\s*(?:\*\w+))*\]) values in (\w+)$/,
    async function (inputIDs: string, module) {

        // Navigate to specified module
        await chooseModule(module);
        await seedbed.client.driver.waitForApp();
        const listView = await seedbed.components[`${module}List`].ListView;

        // Toggle selected records
        await toggleSpecifiedRecords(inputIDs, listView);

        // Select Recalculate Values
        await listView.selectAction('Recalculate Values');
        this.driver.waitForApp();

        // Close Alert
        await closeAlert();
        this.driver.waitForApp();

    }, {waitForApp: true}
);
