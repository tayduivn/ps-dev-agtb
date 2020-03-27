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

import * as _ from 'lodash';
import {thenStepsHelper, Then, stepsHelper} from '@sugarcrm/seedbed';
import ListView from '../views/list-view';
import {TableDefinition} from 'cucumber';
import BaseListView from '../views/baselist-view';

/**
 * Step verifies fields visible on a cached list view for the cached record.
 *
 * @example "I verify fields for *Account_A in #AccountsList:"
 */
Then(/^I verify fields for (\*[a-zA-Z](?:\w|\S)*) in (#[a-zA-Z](?:\w|\S)*)$/,
    async function(record: { id: string }, view: BaseListView, data: TableDefinition) {

        let listItem = view.getListItem({id: record.id});

        let fildsData: any = data.hashes();

        let errors = await listItem.checkFields(fildsData);

        let message = '';
        _.each(errors, (item) => {
            message += item;
        });

        if (message) {
            throw new Error(message);
        }

    }, {waitForApp: true});

/**
 * Verify record exists on #View
 *
 * @example "I should see *Account_A in #AccountsList"
 */
Then(/^I should (not )?see (\*[a-zA-Z](?:\w|\S)*) in (#[a-zA-Z](?:\w|\S)*)$/,
    async function(not, record: { id: string }, view: BaseListView) {

        let listItem = view.getListItem({id: record.id});

        let value = await listItem.isVisibleView();

        if (_.isEmpty(not) !== value) {
            throw new Error('Expected ' + (not || '') + ' to see list item (' + listItem.$() + ')');
        }

    }, {waitForApp: true});

Then(/^I should be redirected to \"(.*)\" route/,
    async function(expectedRoute: string): Promise<void> {
        await thenStepsHelper.checkUrlHash(expectedRoute);
    });

Then<string, string>(/^I verify that (#\S+) still looks like (.*)$/,
    async function (component: any, fileName: any): Promise<void> {
    await stepsHelper.verifyElementByImage(component, fileName);
});
