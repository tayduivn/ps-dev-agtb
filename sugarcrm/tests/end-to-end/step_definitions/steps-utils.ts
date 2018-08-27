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

import {TableDefinition} from 'cucumber';
import * as _ from 'lodash';
import {seedbed} from '@sugarcrm/seedbed';
import AuditLogDrawerLayout from '../layouts/audit-log-drawer-layout';
import PersonalInfoDrawerLayout from '../layouts/personal-info-drawer-layout';

/**
 * Click header button
 *
 * @param layout
 * @param {string} btnName
 * @returns {Promise<void>}
 */
export const headerButtonClick = async function (layout, btnName: string) {
    await layout.HeaderView.clickButton(btnName);
    await seedbed.client.driver.waitForApp();
};

/**
 * Verify values in audit log. Note: only latest entry of the value is verified.
 *
 * @param {AuditLogDrawerLayout} layout
 * @param {TableDefinition} data
 * @returns {Promise<void>}
 */
export const auditLogVerification = async function (layout: AuditLogDrawerLayout, data: TableDefinition) {
    let errors = [];

    const rows = data.rows();

    for (let i = 0; i < rows.length; i++) {
        let row = rows[i];
        let expectedBefore = row[1].trim();
        let expectedAfter = row[2].trim();

        const fieldName = row[0];
        let valueBefore = await layout.getCellValue(fieldName, 'before');
        let valueAfter = await layout.getCellValue(fieldName, 'after');

        // If field is changed more than once it will appear multiple times in Audit Log file.
        // Only the most recent change of the field value is checked
        let latestValueBefore = Array.isArray(valueBefore) ? valueBefore[0] : valueBefore;
        let latestValueAfter = Array.isArray(valueAfter) ? valueAfter[0] : valueAfter;

        if (expectedBefore !== latestValueBefore) {
            errors.push(
                new Error(
                    [
                        `Field '${row[0]}' should be`,
                        `\t'${expectedBefore}'`,
                        `instead of`,
                        `\t'${latestValueBefore}'`,
                        `\n`,
                    ].join('\n')
                )
            );
        }
        if (expectedAfter !== latestValueAfter) {
            errors.push(
                new Error(
                    [
                        `Field '${row[0]}' should be`,
                        `\t'${expectedAfter}'`,
                        `instead of`,
                        `\t'${latestValueAfter}'`,
                        `\n`,
                    ].join('\n')
                )
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
};

export const personalInfoDrawerVerification = async function(layout: PersonalInfoDrawerLayout, data: TableDefinition) {

    let errors = [];
    const rows = data.rows();

    for (let i = 0; i < rows.length; i++) {
        let row = rows[i];
        let expected = row[1].trim();
        let value = await layout.getFieldValue(row[0].trim());

        if (expected !== value) {
            errors.push(
                new Error(
                    [
                        `Field '${row[0]}' should be`,
                        `\t'${expected}'`,
                        `instead of`,
                        `\t'${value}'`,
                        `\n`,
                    ].join('\n')
                )
            );
        }
    }

    let message = '';
    _.each(errors, (item) => {
        message += item.message;
    });

    if (message) {
        throw new Error(message);
    }
}
