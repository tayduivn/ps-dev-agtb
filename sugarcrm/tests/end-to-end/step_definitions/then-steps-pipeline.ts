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

import {seedbed, Then} from '@sugarcrm/seedbed';
import * as _ from 'lodash';
import {TableDefinition} from 'cucumber';
import pipelineView from '../views/pipeline-view';
import {parseInputArray} from './general_bdd';

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
                );
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
 *  Verify if record(s) belong to a particular column
 *
 *  @example
 *  Then I verify the [*Opp_1] records are under "Qualification" column in #OpportunitiesPipelineView view
 */
Then(/^I verify the (\[(?:\*\w+)(?:,\s*(?:\*\w+))*\]) records are (not )?under "(\w+[\/\s\+]*\w+)" column in (#[a-zA-Z](?:\w|\S)*) view$/,
    async function (inputIDs: string, not, columnName: string, view: any) {

        let value;
        let errors = [];
        let recordIds = await parseInputArray(inputIDs);

        let uid = inputIDs.slice(1, inputIDs.length - 1).split(',');

        if (columnName.search('now') !== -1 ) {
            columnName =  seedbed.support.fixDateInput(columnName, "MMMM YYYY");
        }

        for (let i = 0; i < recordIds.length; i++) {

            let listItem = await view.getListItem({id: recordIds[i].id});
            value = await listItem.checkTileViewColumn(columnName);

            if (_.isEmpty(not) && value === false) {
                errors.push(
                    [
                        `The record '${uid[i]}'`,
                        `is expected but not found under the '${columnName}' column in Tile View\n`,
                    ].join('\n')
                );
            } else if ( (!_.isEmpty(not)) && value === true) {
                errors.push(
                    [
                        `The record '${uid[i]}'`,
                        `is not expected but present under the '${columnName}' column in Tile View`,

                        `\n`,
                    ].join('\n')
                );
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
 *  Verify tile delete button state
 *
 *  @example
 *    Then I verify *Opp_1 tile delete button state in #OpportunitiesPipelineView view
 *      | Disabled |
 *      | false    |
 */
Then(/I verify (\*[a-zA-Z](?:\w|\S)*) tile delete button state in (#[a-zA-Z](?:\w|\S)*) view$/,
    async function (record: { id: string }, view: any, data: TableDefinition) {

        let errors = [];
        let row = data.rows()[0];
        let expectedValue  = row[0];

        if (data.hashes.length > 1) {
            throw new Error('One line data table entry is expected');
        }

        let listItem = await view.getListItem({id: record.id});
        let value = await listItem.isDeleteButtonDisabled();

        if (value.toString() !== expectedValue) {
            errors.push(
                [
                    `The state of the delete button expected to be`,
                    `\t'${expectedValue}'`,
                    `instead of`,
                    `\t'${value.toString()}'`,
                    `\n`,
                ].join('\n')
            );
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
 *  Verify column headers in the pipeline view
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
            let expected = rows[i - 1][0];
            let value = await view.getColumnHeader(expected);

            if (!value) {
                errors.push(
                    [
                        `The colum with the name ${expected} does not exists.`,
                        `\n`,
                    ].join('\n')
                );
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
