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

import {givenStepsHelper, whenStepsHelper, stepsHelper, Given} from '@sugarcrm/seedbed';
import {TableDefinition} from 'cucumber';
import ModuleMenuCmp from '../components/module-menu-cmp';
import ListView from '../views/list-view';
import RecordView from '../views/record-view';
import {Utils, When, Then, seedbed} from '@sugarcrm/seedbed';
import BaseView from '../views/base-view';
import * as _ from 'lodash';
import AlertCmp from '../components/alert-cmp';
import BusinessRulesDesign from '../layouts/business-rules-record-layout';
import {chooseModule, chooseRecord, recordViewHeaderButtonClicks, goToUrl} from "./general_bdd";

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
            {module:'pmse_Business_Rules'}
        );
        await businessRulesVerification(layout, table);
    }, {waitForApp: true}
);

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
}

const toArray = async function(values) {
    let array = [];
    if (Array.isArray(values)) {
        array = values;
    } else {
        array.push(values);
    }
    return array;
}

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
