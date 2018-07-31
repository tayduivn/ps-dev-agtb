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
import {seedbed} from '@sugarcrm/seedbed/seedbed';
import RecordLayout from '../layouts/record-layout';
import AlertCmp from '../components/alert-cmp';
import * as _ from 'lodash';

/**
 * Open the specified subpanel on a specified record view
 *
 * @example "I open the tasks subpanel on #Account_ARecord view"
 */
When(/^I open the (\S+) subpanel on (#\S+) view$/,
    async function(
        subpanelName: string,
        recordLayout: RecordLayout,
    ): Promise<void> {
        await recordLayout.SubpanelsLayout.openSubpanel(subpanelName);
    }, {waitForApp: true});

When(/^I (create_new|link_existing) record from (\S+) subpanel on (#\S+) view$/,
    async function(

        actionName: string,
        subpanelName: string,
        recordLayout: RecordLayout,
    ): Promise<void> {
        if (actionName === 'create_new') {
            await recordLayout.SubpanelsLayout.createRecord(subpanelName);
        } else if (actionName === 'link_existing') {
            await recordLayout.SubpanelsLayout.linkRecord(subpanelName);
        }
    }, {waitForApp: true});

/**
 * Link record specified by ID to the subpanel
 *
 * @example "I link existing record *John to leads subpanel on #DP_1Record view"
 */
When(/^I link existing record (\*[a-zA-Z](?:\w|\S)*) to (\S+) subpanel on (#\S+) view$/,
    async function(

        record: {id: string},
        subpanelName: string,
        recordLayout: RecordLayout,

    ): Promise<void> {

        const controlName = 'checkbox';
        const buttonName = 'Add';

        // Open subpanel
        await recordLayout.SubpanelsLayout.openSubpanel(subpanelName);
        await this.driver.waitForApp();

        // Select 'Link Existing' action from the subpanel
        await recordLayout.SubpanelsLayout.linkRecord(subpanelName);
        await this.driver.waitForApp();

        // Toggle checkbox in Search And Add Drawer
        let listItem = seedbed.components[`${_.capitalize(subpanelName)}SearchAndAdd`].ListView.getListItem({id: record.id});
        await listItem.clickItem(controlName);
        await this.driver.waitForApp();

        // Click Add button in Search And Add drawer
        await recordLayout.HeaderView.clickButton(buttonName);
        await this.driver.waitForApp();

        // Close Alert
        let alert = new AlertCmp({});
        await alert.close();
        await this.driver.waitForApp();

    }, {waitForApp: true});
