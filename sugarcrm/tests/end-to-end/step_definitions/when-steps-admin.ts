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

import {seedbed, When} from '@sugarcrm/seedbed';
import AdminMenuCmp from '../components/admin-menu-cmp';
import AdminPanelLayout from '../layouts/admin-panel-layout';
import BWCEditView from '../views/bwc-edit-view';
import UserProfileLayout from '../layouts/user-profile-layout';
import UserProfileView from '../views/user-profile-view';

When<string, any>(
    /^I click on (\S+) button on (#\S+)$/,
    async function(btnName: string, layout: {clickButton: ((btnName: string) => any)}) {
        await navigateToFrame('bwc-frame');
        await layout.clickButton(btnName);
        await this.driver.pause(4000);
        await this.driver.frame(null);
    },
    { waitForApp: true }
);

When<string>(
    /^I choose (\w+) in the user actions menu$/,
    async function(itemName: string): Promise<void> {
        let mainMenuCmp: AdminMenuCmp = seedbed.components.AdminMenuCmp;

        await this.driver.waitForApp();
        await mainMenuCmp.open();
        await mainMenuCmp.clickItem(itemName.toLowerCase());
    },
    { waitForApp: true }
);

When<string, AdminPanelLayout>(
    /^I click on (\w+) link in (#AdminPanel)$/,
    async function(linkName: string, layout: AdminPanelLayout): Promise<void> {
        await navigateToFrame('bwc-frame');
        await layout.AdminPanelView.clickLink(linkName);
        await this.driver.pause(3000);
        await navigateToFrame(null);
    },
    { waitForApp: false }
);

When<BWCEditView, string>(
    /^I set (\S+) (\S+) with "(.*)" value on (#\S+)$/,
    async function(
        fieldName: string,
        fieldType: string,
        value: string,
        editView: BWCEditView
    ) {
        await navigateToFrame('bwc-frame');
        let field = await editView.getField(fieldName, fieldType);
        await field.setValue(value);
        await this.driver.waitForAnimation();
        await this.driver.execSync('blurActiveElement');
        await navigateToFrame(null);
    },
    { waitForApp: true }
);

When<BWCEditView, string>(
    /^I toggle (\S+) (\S+) on (#\S+)$/,
    async function(
        fieldName: string,
        fieldType: string,
        editView: BWCEditView
    ) {
        await navigateToFrame('bwc-frame');
        let field = await editView.getField(fieldName, fieldType);
        await field.setValue('true');
        await this.driver.waitForAnimation();
        await this.driver.execSync('blurActiveElement');
        await navigateToFrame(null);
    },
    { waitForApp: true }
);


When<UserProfileLayout, string>(
    /^I change (\S+) (\S+) with "(.*)" value(?: on "(\w+)" tab)? in (#\S+)$/,
    async function(
        fieldName: string,
        fieldType: string,
        value: string,
        tabName: string,
        layout: UserProfileLayout
    ) {
        await navigateToFrame('bwc-frame');
        await layout.UserProfileView.clickButton('edit');
        await this.driver.pause(2000);

        if (tabName) {
            await layout.UserProfileView.selectTabByName(tabName);
        }

        let field = await layout.UserProfileView.getField(fieldName, fieldType);
        await field.setValue(value);
        await this.driver.execSync('blurActiveElement');
        await navigateToFrame(null);
    },
    { waitForApp: true }
);

When<string, UserProfileLayout>(
    /^I select (User Profile|Password|Advanced|External Accounts|Downloads) tab in (#\S+)$/,
    async function(tabName: string, layout: UserProfileLayout): Promise<void> {

        await navigateToFrame('bwc-frame');
        let view = layout.UserProfileView;
        await view.clickButton('edit');
        await this.driver.pause(2000);

        await view.selectTabByName(tabName);
        await navigateToFrame(null);
    },
    { waitForApp: true }
);

/**
 * Navigate to specific frame
 *
 * @param frameName
 */
export const navigateToFrame = async function (frameName: any) {

    if (frameName !== null ) {
        await seedbed.client.driver.waitForVisible(`#${frameName}`);
        await seedbed.client.driver.pause(1000);
    }
    await seedbed.client.driver.frame(frameName);
    await seedbed.client.driver.pause(2000);
};
