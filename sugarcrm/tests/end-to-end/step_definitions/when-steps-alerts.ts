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
import {closeAlert, closeWarning, verifyAlertProperties} from './general_bdd';

    When(/^I (Cancel|Confirm) confirmation alert$/, async function(choice: string) {
        await closeWarning(choice);
    }, {waitForApp: true});

    When(/^I close alert$/, async function() {
        await closeAlert();
    }, {waitForApp: true});

    When(/^I check alert/, async function(data: TableDefinition) {
        await verifyAlertProperties(data);
    }, {waitForApp: true});
