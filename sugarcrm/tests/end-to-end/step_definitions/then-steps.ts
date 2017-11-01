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

import BaseView from '../views/base-view';
import {Then} from '@sugarcrm/seedbed';
import * as _ from 'lodash';
import {TableDefinition} from 'cucumber';
import RecordLayout from '../layouts/record-layout';
import QliTable from "../views/qli-table";

/**
 * Check whether the cached view is visible
 * Note: for *Edit and *Detail views the opened url is checked to have an id form the cached record
 *
 * @example "I should see #AccountsList view"
 */
Then(/^I should (not )?see (#\S+) view$/,
    async (not, view: BaseView) => {
        let isVisible = await view.isVisibleView();

        if (!not !== isVisible) {
            throw new Error('Expected ' + (not || '') + 'to see "' + view.$() + '" view(layout)');
        }

    });

/**
 * Verifies fields visible on a cached view for the cached record.
 *
 * @example "I verify fields on #Account_APreview.PreviewView"
 */
Then(/^I verify fields on (#[a-zA-Z](?:\w|\S)*)$/,
    async (view: BaseView, data: TableDefinition) => {

        let fildsData: any = data.hashes();

        let errors = await view.checkFields(fildsData);

        let message = '';
        _.each(errors, (item) => {
            message += item;
        });

        if (message) {
            throw new Error(message);
        }

    });


Then(/^I verify fields on QLI total header on (#[a-zA-Z](?:\w|\S)*) view$/, async function (view: RecordLayout, data: TableDefinition) {

    let fildsData: any = data.hashes();

    let errors = await view.QliTable.checkFields(fildsData);

    let message = '';
    _.each(errors, (item) => {
        message += item;
    });

    if (message) {
        throw new Error(message);
    }
});

Then(/^I verify fields for (\d+) row for (#\S+)$/, async function (recordIndex, view: QliTable, data: TableDefinition) {

    let fildsData: any = data.hashes();

    const record = view.getRecord(recordIndex);

    let errors = await record.checkFields(fildsData);

    let message = '';
    _.each(errors, (item) => {
        message += item;
    });

    if (message) {
        throw new Error(message);
    }

});