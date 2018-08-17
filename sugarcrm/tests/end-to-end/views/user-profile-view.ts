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

import * as path from 'path';
import BaseView from '../views/base-view';

/**
 * Represents User Profile view
 *
 * @class UserProfileView
 * @extends BaseView
 */
export default class UserProfileView extends BaseView {
    constructor(options) {
        super(options);
        this.selectors = this.mergeSelectors({
            buttons: {
                edit: '#edit_button',
                save: '#SAVE_HEADER',
                cancel: '#CANCEL_HEADER',
                reset: '#reset_user_preferences_header',
            },

            tabs: {
                $: '#EditView_tabs',
                user_profile: '#tab1',
                password: '#tab2',
                advanced: '#tab4',
                external_accounts: '#tab5',
            },
            avatar: '#picture',

            field_input: 'input[name="{{name}}"]',
        });
    }

    /**
     * Change user's avatar in User Profile tab of user preferences
     *
     * @param {string} iconName
     */
    public uploadAvatar(iconName: string): WebdriverIO.Client<void> {
        let filePath = path.resolve(
            __dirname,
            '../test_files/' + iconName + '.jpg'
        );

        return this.driver.chooseFile(this.$('avatar'), filePath).pause(2000);
    }

    /**
     * Select tab by name when User Profile is in edit mode
     *
     * @param {string} tabName
     */
    public async selectTabByName(tabName: string) {
        tabName = tabName.replace(' ', '_').toLowerCase();
        let selector  =  this.$(`tabs.${tabName}`);
        await this.driver.click(selector);
    }
}
