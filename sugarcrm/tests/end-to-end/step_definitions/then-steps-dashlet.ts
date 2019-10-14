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
import DashletView from '../views/dashlet-view';

/**
 *  Verify "No data available." message is displayed in dashlet when there has no record
 *
 *  @example
 *  Then I verify 'No data available.' message appears in #Dashboard.InactiveTasksDashlet
 */
Then(/^I verify '(No data available.)' message appears in (#\S+)*$/,
    async function (expectedMessage: string, view: DashletView): Promise<void> {
        let actualValue = await view.getDashletFooterMessage();
        if (expectedMessage !== actualValue) {
            throw new Error(`Expected value '${expectedMessage}' does not match actual value '${actualValue}'`);
        }
    }, {waitForApp: true});

/**
 *  Verify that dashlet title is updated
 *
 *  @example
 *  Then I verify 'History Update' title updated in #Dashboard.HistoryDashlet
 */
Then(/^I verify '([a-zA-Z](?:\w|\S\ )*)' title updated in (#\S+)$/,
    async function (expectedLabel: string, view: DashletView): Promise<void> {
    let actualValue = await view.getDashletHeader();
    if (expectedLabel !== actualValue) {
        throw new Error(`Expected value '${expectedLabel}' does not match actual value '${actualValue}'`);
    }
}, {waitForApp: true});
