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

import {When} from '@sugarcrm/seedbed';
import LeadConversionView from '../views/lead-conversion-view';
import RecordLayout from '../layouts/record-layout';


/**
 * Click controls in the Leads Convert drawer
 *
 * @example "I click CreateRecord button on #LeadConversionDrawer.OpportunityContent"
 */
When(/^I click (CreateRecord|Reset|ChevronDown|ChevronUp|SelectRecord) button on (#\S+)$/,
    async function (btnToClick: string, view: LeadConversionView): Promise<void> {

        // Workround: In order to make Select Account button enabled and clickable, we need to click in filter search bar first
        if (btnToClick === 'SelectRecord') {
            await view.searchBarClick();
            await this.driver.waitForApp();
        }

        await view.btnClick(btnToClick.toLowerCase());

    }, {waitForApp: true});

/**
 * Click or Preview record created by lead conversion
 *
 * @example "I preview *A1 record on #JohnRecord"
 */
When(/^I (click|preview) (\*[a-zA-Z](?:\w|\S)*) record on (#\S+)$/,
    async function (action, record: any, layout: RecordLayout): Promise<void> {
        await layout.performAction(action, record.id, record._module);
    }, {waitForApp: true});

