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
// turn of warnings like "Confusing use of '!'., W018"
/* jshint -W018 */

import {TableDefinition} from 'cucumber';
import {When} from '@sugarcrm/seedbed';

export const openMenuAndCheck = async function(layout, needToCheck, data?: TableDefinition) {

    await layout.HeaderView.clickButton('actions');

    if (needToCheck) {

        let rows = data.rows();

        for (let i = 0; i < rows.length; i++) {

            let row = rows[i];
            let buttonName = row[0];

            let isButtonActive = await layout.HeaderView.checkIsButtonActive(buttonName);

            if (row[1] !== isButtonActive.toString()) {

                let errMessage = null;

                if (row[1] === 'true') {
                    errMessage = `menu item '${buttonName}' expected to be active, but it's disabled`;
                } else {
                    errMessage = `menu item '${buttonName}' expected to be disabled, but it's active`;
                }

                throw new Error(errMessage);

            }

        }

    }
};

/**
 * Click header panel buttons
 *
 * @example "I click Save button on #AccountsDrawer header"
 */
When(/^I click (Create|Edit|Cancel|Close|Save|Reply|Add|Complete|MarkForErasure|Reject|Add2Quote|CloseButton|EmailQuote|Commit|SaveDraft|ToggleSidePanel|CreateCategory) button on (#[a-zA-Z](?:\w|\S)*) header$/,
    async function(btnName: string, layout: any) {
        await layout.HeaderView.clickButton(btnName.toLowerCase());
    }, {waitForApp: true});

/**
 * Open header panel actions menu
 *
 * @example "I open actions menu in #Account_ARecord"
 */
When(/^I open actions menu in (#[a-zA-Z]\w+) and check:?$/,
    async function(layout, data: TableDefinition) {

    await openMenuAndCheck(layout, true, data);

    }, {waitForApp: true});

When(/^I open actions menu in (#[a-zA-Z]\w+)$/,
    async function(layout) {

        await openMenuAndCheck(layout, false);

    }, {waitForApp: true});

/**
 * Choose action from actions menu
 *
 * @example "I choose Delete from actions menu in #Account_ARecord"
 */
When(/^I choose (\w+) from actions menu in (#[a-zA-Z]\w+)\s*$/,
    async function(action, layout: any) {
        await layout.HeaderView.clickButton(action);
    }, {waitForApp: true});

