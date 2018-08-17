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

import {Then} from '@sugarcrm/seedbed/';
import UserProfileLayout from '../layouts/user-profile-layout';
import {TableDefinition} from 'cucumber';

/**
 *  Verify value of the specific field with specific field type
 *
 *  @example
 *  Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
 */
Then(
    /^I verify value of (\S+) (\S+) field in (#\S+)$/,
    async function(
        fieldName: string,
        fieldType: string,
        layout: UserProfileLayout,
        data: TableDefinition
    ) {

        if (data.hashes.length > 1) {
            throw new Error('One line data table entry is expected');
        }

        // Get expected value
        const expValue = data.rows()[0][0];

        await this.driver.waitForVisible('#bwc-frame');
        await this.driver.pause(2000);
        await this.driver.frame('bwc-frame');
        await layout.UserProfileView.clickButton('edit');
        await this.driver.pause(2000);

        // Get currently selected items
        let field = await layout.UserProfileView.getField(fieldName, fieldType);
        let value =  await field.getText('');
        await this.driver.execSync('blurActiveElement');
        await this.driver.frame(null);

        // Compare actual and expected values
        if (value !== expValue) {
            throw new Error(`Error! Expected value "${expValue}" and actual value "${value}" do not match!`);
        }
    },
    { waitForApp: true }
);
