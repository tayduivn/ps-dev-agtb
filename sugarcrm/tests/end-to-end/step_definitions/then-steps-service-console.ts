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

import {seedbed, TableDefinition, Then} from '@sugarcrm/seedbed';
import * as _ from 'lodash';
import MultilineListView from '../views/multiline-list-view';
import CsCommentLogDashlet from '../views/cs-comment-log-dashlet-view';
import CsCasesInteractionsDashlet from '../views/cs-cases-interactions-dashlet-view';
import CsCasesInteractionsListView from '../views/cs-cases-interactions-list-view';
import {parseInputArray} from './general_bdd';
import ListViewDashletListView from '../views/list-view-dashlet-list-view';

/**
 *  Verify the order of the item in the multiline list view in Service Console Cases tab
 *
 *  @example
 *  Then I verify case records order in #CasesList.MultilineListView
 *      | record_identifier | expected_list_order |
 *      | C_3               | 3                   |
 *      | C_2               | 2                   |
 *      | C_1               | 1                   |
 */
Then(/^I verify the case records order in (#\S+)$/,
    async function (view: MultilineListView, data: TableDefinition) {

        let rows = data.rows();
        let orderExp: number;
        let orderActual;
        let errors = [];

        for (let i = 0; i < rows.length; i++) {
            let row = rows[i];
            orderExp = Number.parseInt(row[1], 10);

            // Get record object by record name
            let record = await seedbed.cachedRecords.get(row[0]);

            if (record) {
                // Get list item
                let listItem = view.getListItem({id: record.id});

                // Find
                orderActual = await listItem.getListItemPosition();

                if (orderActual !== orderExp) {
                    errors.push(
                        [
                            `The list item *${row[0]} order in the list does not match the expected order.`,
                            `Expected order (from the list top) is: ${orderExp}`,
                            `\tActual order (from the list top) is: ${orderActual}`,
                            `\n`,
                        ].join('\n')
                    );
                }
            } else {
                throw new Error(`Record with identifier '${row[0]}' is not found in the list`);
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
 *  Verify comments in the comment log (from top to bottom)
 *
 *  @example
 *  Then I verify comments in #Dashboard.CsCommentLogDashlet
 *      | comment                             |
 *      | Add reference to the Account_1      |
 *      | Add reference to the user userLName |
 *      | My second new comment               |
 */
Then(/^I verify comments in (#\S+)$/,
    async function(view: CsCommentLogDashlet, data: TableDefinition) {
        let rows = data.rows();
        let expValue, actValue;
        let errors = [];

        // Click 'View All' button before checking comments
        await view.clickViewAllBtn();

        for (let i = 1; i <= rows.length; i++) {
            let row = rows[i - 1];
            // Retrieve comment value by index
            actValue = await view.getCommentByIndex(i.toString());
            [expValue] = row;
            if (actValue !== expValue) {
                errors.push(
                    [
                        `The comment ${i} (from the top) does not match expected value`,
                        `The expected comment message is: ${expValue}`,
                        `\tThe actual comment message is: ${actValue}`,
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


/**
 *  Verify items in the Cases Interactions dashlet list view (top-to-bottom)
 *
 *      @example
 *      Then I verify list items in #Dashboard.CsCasesInteractionsDashlet
 *          | name            | status   |
 *          | Meeting 1       | Held     |
 *          | Meeting 2       | Not Held |
 */
Then(/^I verify list items in (#\S+)$/,
    async function(view: CsCasesInteractionsDashlet, data: TableDefinition) {
        let rows = data.rows();
        let expValue = {name: '', status: ''};

        let actValue;
        let errors = [];

        for (let i = 1; i <= rows.length; i++) {
            let row = rows[i - 1];
            // Retrieve comment value by index
            actValue = await view.getActivityInfo(i);
            expValue.name = row[0];
            expValue.status = row[1];
            if (actValue.name !== expValue.name ) {
                errors.push(
                    [
                        `The expected and actual record names don't match:`,
                        `The expected activity name is: ${expValue.name}`,
                        `\tThe actual activity name is: ${actValue.name}`,
                        `\n`,
                    ].join('\n')
                );
            } else if (actValue.status !== expValue.status) {
                errors.push(
                    [
                        `The expected and actual record statuses don't match:`,
                        `The expected activity status is: ${expValue.status}`,
                        `\tThe actual activity status is: ${actValue.status}`,
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


/**
 *  Verify record info in the expanded block inside Cases Interactions dashlet
 *
 *      @example
 *      Then I verify *M_1 record info in #Dashboard.CsCasesInteractionsDashlet.CsCasesInteractionsList
 *          | fieldName   | value                                 |
 *          | name        | Meeting 1                             |
 *          | status      | Held                                  |
 *          | duration    | 12/01/2020 05:00pm - 06:00pm (1 hour) |
 *          | description | Testing with Seedbed                  |
 */
Then(/^I verify (\*[a-zA-Z](?:\w|\S)*) record info in (#\S+)$/,
    async function(record: { id: string }, view: CsCasesInteractionsListView, data: TableDefinition) {
        let rows = data.rows();
        let listItem = view.getListItem({id: record.id});
        let errors = [];

        for (let i = 1; i <= rows.length; i++) {
            let row = rows[i - 1];
            // Retrieve comment value by index

            let fieldName = row[0];
            let expValue = row[1];
            let value = await listItem.getExtendedInteractionInfo(i);

            if (value !== expValue) {
                errors.push(
                    [
                        `The expected and actual fields of record "${record}" don't match:`,
                        `The expected value of the field "${fieldName}" is: ${expValue}`,
                        `\tThe actual value of the field "${fieldName}" is: ${value}`,
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

/**
 *  Verify if record(s) are present/not present on the list view dashlet
 *
 *  @example
 *  Then I should see [*T1, *T2, *T3, *T4, *T5] on #Dashboard.CsDashableRecordDashlet.ListView dashlet
 *
 */
Then(/^I should (not )?see (\[(?:\*\w+)(?:,\s*(?:\*\w+))*\]) on (#\S+) dashlet$/,
    async function(not: string, inputIDs: string, listView: ListViewDashletListView ) {

        // Parse input array
        let recordIds = await parseInputArray(inputIDs);

        // Verify whether record exists on the list view or not
        for (let record of recordIds) {
            let listItem = listView.getListItem({id: record.id});

            let value = await listItem.isVisibleView();

            if (_.isEmpty(not) !== value) {
                throw new Error('Expected ' + (not || '') + ' to see list item (' + listItem.$() + ')');
            }
        }
    }, {waitForApp: true}
);
