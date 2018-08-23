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

import {When, seedbed} from '@sugarcrm/seedbed';
import {TableDefinition} from 'cucumber';
import * as _ from 'lodash';
import AlertCmp from '../components/alert-cmp';

/**
 * Complete or Reject a Data Privacy Request and confirm
 *
 * @example When I reject the Data Privacy request on #DP_1Record
 * @example When I complete the Data Privacy request on #dp01Record (complete, non-erasure)
 */
When(/^I (eraseandcomplete|reject|complete) the Data Privacy request on (#[a-zA-Z](?:\w|\S)*)$/,
    async function (action:string, layout: any) {
        await layout.HeaderView.clickButton(action);
        await this.driver.waitForApp();

        // Click Confirm in Confirmation alert
        let alert = new AlertCmp({type: 'warning'});
        await alert.clickButton('confirm');
        await this.driver.waitForApp();

        // Close Confirmation Alert
        alert = new AlertCmp({});
        await alert.close();
        await this.driver.waitForApp();

    }, {waitForApp: true});

/**
 * Open Personal Info drawer and mark fields to be erased
 *
 * @example     When I select fields for erasure for *Travis record in #DP_1Record.SubpanelsLayout.subpanels.prospects subpanel
 *               | fieledName            |
 *               | first_name            |
 *               | last_name             |
 *               | title                 |
 *               | primary_address_state |
 *
 */
When(/^I select fields for erasure for (\*[a-zA-Z](?:\w|\S)*) record in (#\S+) subpanel$/,
    async function (

        record: {id: string},
        layout: any,
        data: TableDefinition):

        Promise<void> {

        const menuItemName = 'MarkToErase';
        const buttonName = 'markforerasure';

        let listItem = await layout.getListItem({id: record.id});

        // Open Actions drop-down and select 'Mark To Erase' menu item
        await listItem.openDropdown();
        await this.driver.waitForApp();
        await listItem.clickListButton(menuItemName);
        await this.driver.waitForApp();

        // Check for one line data table
        if (data.hashes.length > 1) {
            throw new Error('One line data table entry is expected');
        }

        // Process table of fields marked for erasure
        const rows = data.rows();
        for (let i = 0; i < rows.length; i++) {
            await seedbed.components.PersonalInfoDrawer.clickRowByFiledName(data.rows()[i]);
            await this.driver.waitForApp();
        }

        // Click 'Mark For Erasure'
        await seedbed.components[`${_.capitalize(layout.link)}SearchAndAdd`].HeaderView.clickButton(buttonName);
        await this.driver.waitForApp();

    }, {waitForApp: true});
