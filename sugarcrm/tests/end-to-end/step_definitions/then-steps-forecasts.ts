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

import {Then} from '@sugarcrm/seedbed';
import * as _ from 'lodash';
import {TableDefinition} from 'cucumber';
import ForecastsFooter from '../views/forecasts-footer';
import ForecastsTopInfoBar from '../views/forecasts-top-info-bar-view';
import ForecastsManagerFooterView from '../views/forecasts-manager-footer-view';

/**
 * Verify amounts displayed in top info bar
 *
 * Note: This step definition applicable for both Sales Rep and Sales Manager worksheets
 *
 *      @example
 *      Then I verify forecasts data on #Forecasts.SalesManagerWorksheet.TopInfoBar
 *          | fieldName   | value     |
 *          | quota       | $1,000.00 |
 *          | worst_case  | $800.00   |
 *          | likely_case | $1,000.00 |
 *          | best_case   | $1,200.00 |
 */
Then(/^I verify forecasts data on (#\S+)$/, async function (view: ForecastsTopInfoBar, data: TableDefinition) {
    let fieldsData: any = data.hashes();
    await view.checkFields(fieldsData);
});

/**
 *      Verify totals in Forecasts Sales Rep worksheet footer
 *
 *      @example:
 *      Then I verify forecasts Displayed Total data on #Forecasts.SalesRepWorksheet.Footer
 *          | fieldName | value   |
 *          | Best      | $400.00 |
 *          | Likely    | $300.00 |
 */
Then(/^I verify forecasts (Displayed Total|Overall Total) data on (#\S+)$/, async function (totalType, view: ForecastsFooter, data: TableDefinition) {

    let expectedData: any = data.hashes();
    let result = await view.getFooterValue(totalType);
    let amountType;

    for (let i in expectedData) {
        amountType = (expectedData[i].fieldName).toLowerCase();

        if (amountType === 'likely') {
            if (result.likely !== expectedData[i].value) {
                throw new Error(`${totalType} ${amountType} amount failure! Expected: ${expectedData[i].value} Actual: ${result.likely}`);
            }
        } else if (amountType === 'best') {
            if (result.best !== expectedData[i].value) {
                throw new Error(`${totalType} ${amountType} amount failure! Expected: ${expectedData[i].value} Actual: ${result.best}`);
            }
        }
    }
});

/**
 * Verify totals in Forecasts Sales Manager Worksheet Footer
 *
 *      @example:
 *      Then I verify forecasts Total amounts in #Forecasts.SalesManagerWorksheet.Footer
 *          | fieldName            | value     |
 *          | quota                | $1,000.00 |
 *          | worst_case           | $600.00   |
 *          | worst_case_adjusted  | $800.00   |
 */
Then(/^I verify forecasts Total amounts in (#\S+)*$/,
    async function (view: ForecastsManagerFooterView, data: TableDefinition): Promise<void> {
        let rows = data.rows();
        let errors = [];

        await this.driver.waitForApp();

        for (let i = 0; i < rows.length; i++) {
            let expected = rows[i][1];
            let value = await view.getFooterFieldValue(rows[i][0].toLowerCase());
            if (value !== expected) {
                errors.push(
                    [
                        `Expected amout: ${expected}`,
                        `\tActual amount:  ${value}`,
                        `\n`,
                    ].join('\n')
                );
            }
        }
        let message = '';
        _.each(errors, (item) => {
            message += item;
        });

        if (message) {
            throw new Error(message);
        }
    }, {waitForApp: true});
