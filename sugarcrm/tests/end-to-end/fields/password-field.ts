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

import BaseField from './text-field';
import {seedbed} from '@sugarcrm/seedbed';

/**
 * @class PasswordField - for portal credentials on contact record view
 * @extends BaseField
 */

export default class extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                $: '',
                input_new_password: 'input[name=new_password]',
                input_retype_password: "input[name=confirm_password]",
                changePasswordLink: '.togglePasswordFields',
            }
        });
    }

    public async setValue(val: any): Promise<void> {

        // Check if 'Change Password' link is present in Contact record drawer when create new or edit existing contact record
        let isElementExists = await this.driver.isElementExist(this.$('field.changePasswordLink'));

        // If 'Change Password' link does not exist in Contact drawer, we assume that a brand new password is created
        // Otherwise we update previously existed password and to do that the 'Change Password' link must be clicked first
        if (isElementExists) {
            await this.driver.click(this.$('field.changePasswordLink'));
            await this.driver.waitForApp();
        }

        // Type and re-type a new password
        await this.driver.setValue(this.$('field.input_new_password'), val);
        await this.driver.waitForApp();
        await this.driver.setValue(this.$('field.input_retype_password'), val);
        await this.driver.waitForApp();
    }
}
