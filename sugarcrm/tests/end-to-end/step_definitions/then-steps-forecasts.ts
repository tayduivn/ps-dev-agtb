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
import ForecastsListView  from '../views/forecasts-list-view';
import ForecastsFooter from '../views/forecasts-footer';

Then(/^I verify header data on (#\S+)$/, async function ( view: ForecastsListView, data: TableDefinition) {

    let fieldsData: any = data.hashes();
    return view.checkFields(fieldsData);
});


Then(/^I verify (Displayed Total|Overall Total) data on (#\S+)$/, async function (totalType, view: ForecastsFooter, data: TableDefinition) {

    let expectedData: any = data.hashes();
    let result = await view.getFooterValue(totalType);
    let amountType;

    for (let i in expectedData) {
        amountType = (expectedData[i].fieldName).toLowerCase();

        if (amountType === 'likely') {
            if (result.likely != expectedData[i].value) {
                throw new Error(`${totalType} ${amountType} amount failure! Expected: ${expectedData[i].value} Actual: ${result.likely}`);
            }
        }
        if (amountType === 'best') {
            if (result.best != expectedData[i].value) {
                throw new Error(`${totalType} ${amountType} amount failure! Expected: ${expectedData[i].value} Actual: ${result.best}`);
            }
        }
    }
});
