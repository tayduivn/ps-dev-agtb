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
import AlertCmp from '../components/alert-cmp';
import {chooseModule, closeAlert, toggleRecord, parseInputArray} from '../step_definitions/general_bdd';
import MassupdateView from '../views/massupdate-view';

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

        // Get Field Label
        let argumentsArray = [];
        argumentsArray.push(fieldName, module);
        let fieldLabel = await seedbed.client.driver.execSync('getLabelByFieldName', argumentsArray);

        // Set value for field selector ('parent field')
        await massUpdateView.setParentFieldValue(fieldLabel.value, i + 2);

        // Set new value for the selected field ('child field')
        let inputData = new Hashmap();
        inputData.push(fieldName, fieldValue);
        await massUpdateView.setFieldsValue(inputData);

        // Get Field object
        let fieldObject = await seedbed.client.driver.execSync('getFieldDef', argumentsArray);

        // Dismiss confirmation alert for fields of 'populate_list' type
        if (fieldObject.value.populate_list && (fieldName.indexOf('account_name') !== -1 )) {
            let alert = new AlertCmp({type: 'warning'});
            await alert.clickButton('confirm');
        }
    }
};

const buttonClickMassUpdate = async function (btnName: string, view: MassupdateView) {
    return view.performAction(btnName.toLowerCase());
};

When(/^I (perform|cancel) mass update of (\w+) (\[(?:\*\w+)(?:,\s*(?:\*\w+))*\]) with the following values:$/,
    async function(button: string, module, inputIDs: string, table: TableDefinition) {

        await chooseModule(module);
        const listView = await seedbed.components[`${module}List`].ListView;
        const massUpdateView = await seedbed.components[`${module}List`].MassUpdateView;

        let recordIds = null;
        recordIds = await parseInputArray(inputIDs);

        // toggle specific record(s)
        if (!Array.isArray(recordIds)) {
            await toggleRecord({id: recordIds.id}, listView);
        } else {
            for (let i = 0; i < recordIds.length; i++) {
                await toggleRecord({id: recordIds[i].id}, listView);
            }
        }

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
 */

When(/^I recalculate (\[(?:\*\w+)(?:,\s*(?:\*\w+))*\]) values in (\w+)$/,
    async function (inputIDs: string, module) {

        // Navigate to specified module
        await chooseModule(module);
        await seedbed.client.driver.waitForApp();
        const listView = await seedbed.components[`${module}List`].ListView;

        let recordIds = null;
        recordIds = await parseInputArray(inputIDs);

        // toggle specific record(s)
        if ( !Array.isArray(recordIds) ) {
            await toggleRecord({id : recordIds.id}, listView );
        } else {
            for ( let i = 0; i < recordIds.length; i++) {
                await toggleRecord({id : recordIds[i].id}, listView );
            }
        }

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
