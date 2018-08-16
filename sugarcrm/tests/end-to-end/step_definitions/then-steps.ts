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
import {Then, seedbed} from '@sugarcrm/seedbed';
import * as _ from 'lodash';
import {TableDefinition} from 'cucumber';
import ListView from '../views/list-view';
import RecordsMarkedForErasureDashlet from '../views/records-marked-for-erasure-dashlet';

/**
 * Check whether the cached view is visible
 * Note: for *Edit and *Detail views the opened url is checked to have an id form the cached record
 *
 * @example "I should see #AccountsList view"
 */
Then(/^I should (not )?see (#\S+) view$/,
    async function (not, view: BaseView) {
        let isVisible = await view.isVisibleView();

        if (!not !== isVisible) {
            throw new Error('Expected ' + (not || '') + 'to see "' + view.$() + '" view(layout)');
        }

    });

/**
 * Verify number of records in the list view
 *
 * @example "I verify number of records in #Opp1Record.SubpanelsLayout.subpanels.meetings is 0"
 */
Then(/^I verify number of records in (#\S+) is (\d+)$/,
    async function (view: ListView, count) {

        let actualCount  = await view.getNumberOfRecords();
        if (actualCount != count) {
            throw new Error('Expected rows ' + count + ' Actual rows: ' + actualCount );
        }
    });


/**
 * Verifies fields visible on a cached view for the cached record.
 *
 * @example "I verify fields on #Account_APreview.PreviewView"
 */
Then(/^I verify fields on (#[a-zA-Z](?:\w|\S)*)$/,
    async function (view: BaseView, data: TableDefinition) {
        const attrRefRegex = RegExp(/\{\*([a-zA-Z](?:\w|\S)*)\.((?:\w|\s)*)}/g);

        /**
         * Replaces references to dynamic values with their value from the
         * cached API response for the specified record.
         *
         * @example "{*Case_1.case_number}" is replaced with "237".
         * | fieldName | value                                |
         * | name      | [CASE:{*Case_1.case_number}] My Case |
         *
         * @param {string} value
         * @return {{}}
         */
        function getReplacementsForAttributeReferences(value: string) {
            let replacements = {};
            let match;
            let recordIdOfReference;
            let apiResponseForRecord;

            // Find the substitutions for every match captured.
            while ((match = attrRefRegex.exec(value)) !== null) {
                recordIdOfReference = seedbed.cachedRecords.get(match[1]).id;
                apiResponseForRecord = seedbed.api.created.find((rec) => {
                    return recordIdOfReference === rec.id;
                });

                if (apiResponseForRecord) {
                    replacements[match[0]] = apiResponseForRecord[match[2]];
                }
            }

            return replacements;
        }

        // Substitute the references in the values for all fields where one or
        // more references are found.
        const fieldsData: any = _.map(data.hashes() || [], (field) => {
            const replacements = getReplacementsForAttributeReferences(field.value);

            _.each(replacements, (value: string, key: string) => {
                field.value = field.value.replace(RegExp(_.escapeRegExp(key), 'g'), value);
            });

            return field;
        });

        let errors = await view.checkFields(fieldsData);
        let message = '';
        _.each(errors, (item) => {
            message += item;
        });

        if (message) {
            throw new Error(message);
        }

    });

Then(/^I verify records on (#[a-zA-Z](?:\w|\S)*)$/,
    async function (view: RecordsMarkedForErasureDashlet, data: TableDefinition) {
        let rows = data.rows();

        for (let i = 0; i < rows.length; i++) {
            let row = rows[i];
            let recordUID = row[0].replace('*', '');
            let amountOfFieldsToErase = Number.parseInt(row[1], 10);

            let record = seedbed.cachedRecords.get(recordUID);

            let value = await view.getAmountOfFieldsToErase(record.id);

            if (amountOfFieldsToErase !== value) {
                throw new Error(`Record ${row[0]}: Expected number of field to erase: ${amountOfFieldsToErase}. Actual fields marked to erase: ${value}.`);
            }
        }
    });

Then(/^I verify headers on (#[a-zA-Z](?:\w|\S)*)$/,
    async function (view: RecordsMarkedForErasureDashlet, data: TableDefinition) {
        let rows = data.rows();

        for (let i = 0; i < rows.length; i++) {
            let row = rows[i];
            let moduleName = row[0];
            let amountOfRecordsToErase = Number.parseInt(row[1], 10);

            let value = await view.getAmountOfRecordsToErase(moduleName);

            if (amountOfRecordsToErase !== value) {
                throw new Error(`Module ${moduleName}: Expected number of records to erase: ${amountOfRecordsToErase}. Actual records marked to erase: ${value}.`);
            }
        }
    });
