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
import {Then, seedbed, stepsHelper} from '@sugarcrm/seedbed';
import * as _ from 'lodash';
import {TableDefinition} from 'cucumber';
import RecordsMarkedForErasureDashlet from '../views/records-marked-for-erasure-dashlet';
import PersonalInfoDrawerLayout from '../layouts/personal-info-drawer-layout';
import RecordLayout from '../layouts/record-layout';
import AuditLogDrawerLayout from '../layouts/audit-log-drawer-layout';
import {headerButtonClick, auditLogVerification, personalInfoDrawerVerification} from './steps-utils';

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
    async function (view: any, count) {

        let actualCount = await view.getNumberOfRecords();
        if (actualCount != count) {
            throw new Error(`Expected rows: ${count}.  Actual rows: ${actualCount}`);
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

Then(/^I verify PII fields in (#\S+) for (#[a-zA-Z](?:\w|\S)*)$/,
    async function (layout: PersonalInfoDrawerLayout, recordlayout: RecordLayout, data: TableDefinition): Promise<void> {

        // Open Actions menu
        await headerButtonClick(recordlayout, 'actions');
        // Select View Personal Info menu item
        await headerButtonClick(recordlayout, 'viewpersonalinfo');
        // Verify field values in Personal Info drawer
        await personalInfoDrawerVerification(layout,data);
        // Close Personal Info drawer
        await headerButtonClick(recordlayout, 'closebutton');

    }, {waitForApp: true});

/**
 *  Verify latest changes of field values in audit log for Old Value and New Value columns.
 *
 *  Note: Record view should be displayed to use this function
 *
 *  @example
 *  Then I verify Audit Log fields in #AuditLogDrawer for #Lead_1Record
 *      | fieldName  | Old Value  | New Value |
 *      | first_name | Novak      | Pete      |
 *      | last_name  | Djokovic   | Sampras   |
 *
 */
Then(/^I verify Audit Log fields in (#AuditLogDrawer) for (#\S+)*$/,
    async function (layout: AuditLogDrawerLayout, recordlayout: RecordLayout, data: TableDefinition): Promise<void> {

        // Open Actions menu
        await headerButtonClick(recordlayout, 'actions');
        // Select Audit Log menu item
        await headerButtonClick(recordlayout, 'auditlog');
        // Perform verifications
        await auditLogVerification(layout, data);
        // Close Audit Log drawer
        await headerButtonClick(recordlayout, 'closebutton');

    }, {waitForApp: true});

/**
 * Screenshot test comparison for different elements on the page
 *
 * @example "I verify that help element from #Footer still looks like footer-help"
 */
Then(/^I verify that (\S+) element from (#\S+) still looks like (.*)$/,
    async function(elementName: string, component: any, fileName: string): Promise<void> {
        // FIXME AT-146: Use proper typechecking on view
        let selector = `elements.${elementName}`;
        let view = component.type ? component.defaultView : component;
        await stepsHelper.verifyElementByImage(view, fileName, selector);
    }, {waitForApp: true});