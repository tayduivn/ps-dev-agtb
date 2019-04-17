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
import * as _ from 'lodash';
import {TableDefinition} from 'cucumber';
import pipelineView from '../views/pipeline-view';

/**
 *  Verify tile content fields value in Pipeline View
 *
 *  @example
 *  Then I verify fields of *Opp_1 tile in #OpportunitiesPipelineView view
 *      | value      |
 *      | Acc_1      |
 *      | 04/19/2019 |
 *      | $2,000.00  |
 *
 */
Then(/^I verify (\*[a-zA-Z](?:\w|\S)*) tile field values in (#[a-zA-Z](?:\w|\S)*) view$/,
    async function (record: { id: string }, view: any, data: TableDefinition) {

        let listItem = view.getListItem({id: record.id});
        let errors = [];
        let value;

        const rows = data.rows();

        for (let i = 0; i < rows.length; i++) {

            let row = rows[i];
            let expectedValue = row[0];

            if (i === 0) {
                value = await listItem.getTileName();
            } else {
                value = await listItem.getTileFieldValue(i);
            }

            if (value !== expectedValue ) {
                errors.push(
                    [
                        `Expected Value: `,
                        `\t'${expectedValue}'`,
                        `Actual Value`,
                        `\t'${value.toString()}'`,
                        `\n`,
                    ].join('\n')
                )
            }
        }

        let message = '';
        _.each(errors, (item) => {
            message += item;
        });

        if (message) {
            throw new Error(message);
        }
    });

/**
 *  Verify column headers in pipeline view
 *
 *  @example
 *  Then I verify pipeline column headers in #LeadsPipelineView view
 *      | value      |
 *      | New        |
 *      | Assigned   |
 *      | In Process |
 *      | Converted  |
 *      | Recycled   |
 *      | Dead       |
 */
Then(/^I verify pipeline column headers in (#\S+) view$/,
    async function (view: pipelineView, data: TableDefinition) {

        let rows = data.rows();
        let errors = [];
        for (let i = 1; i <= rows.length; i++) {
            let expected = rows[i-1][0];
            let value = await view.getColumnHeader(i);

            if (value !== expected) {
                errors.push(
                    [
                        `Expected colum header name: ${expected}`,
                        `\tActual columnheader name: ${value}`,
                        `\n`,
                    ].join('\n')
                )
            }
        }
        let message = '';
        _.each(errors, (item) => {
            message += item;
        });

        if (message) {
            throw new Error(message);
        }

    }, {waitForApp: true});
