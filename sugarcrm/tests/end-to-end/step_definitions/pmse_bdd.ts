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
import {When, Then, seedbed} from '@sugarcrm/seedbed';
import * as _ from 'lodash';
import BusinessRulesDesign from '../layouts/business-rules-record-layout';
import {chooseModule, chooseRecord, closeAlert, closeWarning, recordViewHeaderButtonClicks} from './general_bdd';
import BpmWindowCmp from '../components/bpm-window-cmp';
import {openMenuAndCheck} from './when-steps-record-header';
import RecordLayout from '../layouts/record-layout';

When(/^I begin designing pmse_Business_Rules \*(\w+)$/,
    async function(name: string) {
        let module = 'pmse_Business_Rules';
        await chooseModule(module);
        let view = await seedbed.components[`${module}List`].ListView;
        let record = await seedbed.cachedRecords.get(name);
        await chooseRecord({id : record.id}, view );
        let rec_view = await seedbed.components[`${name}Record`];
        await recordViewHeaderButtonClicks('actions', rec_view);
        await recordViewHeaderButtonClicks('design_pbr', rec_view);
        await seedbed.client.driver.waitForApp();
    }, {waitForApp: true}
);

Then(/^the pmse business rule designer should contain the following values:$/,
    async function(table: TableDefinition) {
        let layout = seedbed.createComponent<BusinessRulesDesign>(
            BusinessRulesDesign,
            {module: 'pmse_Business_Rules'}
        );
        await businessRulesVerification(layout, table);
    }, {waitForApp: true}
);

/**
 *  Close BPM pop-up window
 *
 *  @example
 *  When I close BPM pop-up window
 */
When(/^I close BPM pop-up window$/,
    async function() {

        let bpmWindow = new BpmWindowCmp();
        await bpmWindow.close();
    }, {waitForApp: true}
);

/**
 * Add note to the process in BPM pop-up window
 *
 * @example
 * When I add the following note to the process in BPM pop-up window:
 *  | note        |
 *  | My new Note |
 */
When(/^I add the following note to the process in BPM pop-up window:$/,
    async function(data: TableDefinition) {

        if (data.hashes.length > 1) {
            throw new Error('One line data table entry is expected');
        }

        const rows = data.rows();
        let bpmWindow = new BpmWindowCmp();
        await bpmWindow.addNote(rows[0][0]);
    }, {waitForApp: true}
);

/**
 *  Verify last note in the BPM pop-up window
 *
 *  @example
 *  Then I verify the last note in BPM pop-up window
 *      | note                                       |
 *      | Rejected! Please spend more time fixing it |
 */
Then(/^I verify the last note in BPM pop-up window$/,
    async function(data: TableDefinition) {

        if (data.hashes.length > 1) {
            throw new Error('One line data table entry is expected');
        }

        const rows = data.rows();
        let expectedValue  = rows[0][0];
        let bpmWindow = new BpmWindowCmp();
        let actualValue = await bpmWindow.getLastNote();
        if (expectedValue !== actualValue) {
            throw new Error(`Expected value '${expectedValue}' does not match actual value '${actualValue}'`);
        }
    }, {waitForApp: true}
);

/**
 *  Delete last note in the BPM pop-up window
 *
 *  @example
 *  When I delete last note in BPM pop-up window
 */
When(/^I delete last note in BPM pop-up window$/,
    async function() {
        let bpmWindow = new BpmWindowCmp();
        await bpmWindow.deleteLastNote();
    }, {waitForApp: true}
);

/**
 * Approve or Reject a Business Process request, close confirmation alert
 *
 * @example
 * When I approve the Business Process request on #DP_1Record
 *
 */
When(/^I (approve|reject|route) the Business Process request on (#[a-zA-Z](?:\w|\S)*)$/,
    async function (action: string, layout: any) {
        await layout.HeaderView.clickButton(action);
        await this.driver.waitForApp();

        // Confirm request
        await closeWarning('Confirm');
        // Close success message
        await closeAlert();

    }, {waitForApp: true});

/**
 * Assign new Process user in select new process user window
 *
 * @example
 * When I select new process user "sally"
 */
When(/^I select new process user "(\w+[\s]*\w+)"$/,
    async function (userName: string) {
        let bpmWindow = new BpmWindowCmp();
        await bpmWindow.selectProcessUser(userName);
    }, {waitForApp: true});

/**
 * Select Process routing type in select new process user window
 *
 * @example
 * When I select "Round Trip" process routing type
 */
When(/^I select "(Round Trip|One Way)" process routing type$/,
    async function (type: string) {
        let bpmWindow = new BpmWindowCmp();
        type = type.replace(' ', '_');
        await bpmWindow.selectRoutingType(type.toUpperCase());
    }, {waitForApp: true});

/**
 * Add note to the process in select new process user window
 *
 * @example
 * When I add note "Assign to Sally to take a look"
 */
When(/^I add note "(\w+[\s]*\w+)"$/,
    async function (text: string) {
        let bpmWindow = new BpmWindowCmp();
        await bpmWindow.addText(text);
    }, {waitForApp: true});

/**
 * Click Save or Cancel in the BPM pop-up window
 *
 * @example
 * When I save new BPM settings
 */
When(/^I (save|cancel) new BPM settings$/,
    async function (action: string) {
        let bpmWindow = new BpmWindowCmp();
        await bpmWindow.btnClick(action);
    }, {waitForApp: true});

/**
 * Select new Process User action from select new process user window
 *
 *     @example
 *     When I select new process user action for #Pr_1Record process with the following settings:
 *      | user | type    | note                         |
 *      | jim  | One Way | Assign to Jim to take a look |
 */
When(/^I select new process user action for (#[a-zA-Z]\w+) process with the following settings:$/,
    async function (record: RecordLayout, data: TableDefinition) {
        const action = 'SelectNewProcessUser';

        // Open Actions dropdown
        await openMenuAndCheck(record, false);

        // Select Action from Actions menu
        await record.HeaderView.clickButton(action);

        if (data.hashes.length > 1) {
            throw new Error('One line data table entry is expected');
        }

        // Getting the first row data from table content
        const rows = data.rows();
        const row = rows[0];
        // Getting values from each cell of the first row
        let [newProcessUser, routingType, note] = row;

        let bpmWindow = new BpmWindowCmp();
        await bpmWindow.selectProcessUser(newProcessUser);

        // Select Process routing type: One Way or Round Trip
        if (routingType === 'One Way' || routingType === 'Round Trip') {
            // Replace a space with underscore and change to uppercase
            routingType = routingType.replace(' ', '_');
            await bpmWindow.selectRoutingType(routingType.toUpperCase());
        } else {
            throw new Error('Error: Unsupported routing type!');
        }

        // Add a note
        await bpmWindow.addText(note);

        // Save the changes by click on the Save button
        await bpmWindow.btnClick('save');
        await closeAlert();
    }, {waitForApp: true});

/**
 * Verify Business Rules
 *
 * @param layout
 * @param data
 * @returns {Promise<void>}
 */
const businessRulesVerification = async function (layout: BusinessRulesDesign, data: TableDefinition) {
    let errors = [];
    const rows = data.rows();

    let fieldsArr = await toArray(await layout.getFields());
    let opsArr = await toArray(await layout.getOperators());
    let valuesArr = await toArray(await layout.getValues());

    let fieldCounter = 0, opCounter = 0, valueCounter = 0;
    for (let i = 0; i < rows.length; i++) {
        let row = rows[i];
        if (row[0].startsWith('cd_field') || row[0].startsWith('cc_field')) {
            errors.push(await validate(row[0], row[1].trim(), fieldsArr[fieldCounter]));
            fieldCounter++;
        }
        if (row[0].startsWith('operator')) {
            errors.push(await validate(row[0], row[1].trim(), opsArr[opCounter]));
            opCounter++;
        }
        if (row[0].startsWith('cd_value') || row[0].startsWith('cc_value')) {
            errors.push(await validate(row[0], row[1].trim(), valuesArr[valueCounter]));
            valueCounter++;
        }
    }

    let message = '';
    _.each(errors, (item) => {
        message += item;
    });

    if (message) {
        throw new Error(message);
    }
};

const toArray = async function(values) {
    let array = [];
    if (Array.isArray(values)) {
        array = values;
    } else {
        array.push(values);
    }
    return array;
};

/*
 * Validate value from layout with the expected value
 */
const validate = async function(name, expected, value) {
    if (expected !== value) {
        return new Error(
            [
                `Field '${name}' should be`,
                `\t'${expected}'`,
                `instead of`,
                `\t'${value}'`,
                `\n`,
            ].join()
        );
    }
    return '';
};

