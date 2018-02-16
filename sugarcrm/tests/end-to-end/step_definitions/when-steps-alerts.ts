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

import AlertCmp from '../components/alert-cmp';
import {When} from '@sugarcrm/seedbed';
import {TableDefinition} from 'cucumber';
import * as _ from 'lodash';

    /**
     * Delete confirmation alert
     */
    When(/^I (Cancel|Confirm) confirmation alert$/, async function(choice: string) {
        let alert = new AlertCmp({type: 'warning'});
        await alert.clickButton(choice.toLowerCase());
    }, {waitForApp: true});

    When(/^I close alert$/, async function() {
        let alert = new AlertCmp({});
        await alert.close();
    }, {waitForApp: true});

    When(/^I check alert/, async function(data: TableDefinition) {

        let alert = new AlertCmp();
        let text = await alert.getText();
        let messageType = await alert.getType();

        let expectedData:any = data.hashes()[0];

        let message = expectedData.message;
        let type = expectedData.type;

        if (text !== message) {
            throw new Error("Expected message: " + message + ". Actual message: " + text);
        }

        if (messageType !== type) {
            throw new Error("Expected type: " + message + ". Actual type: " + text);
        }


    }, {waitForApp: true});
