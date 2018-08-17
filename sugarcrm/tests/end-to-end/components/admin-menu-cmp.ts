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

import {BaseView, seedbed} from '@sugarcrm/seedbed';

/**
 * Represents Admin Menu.
 *
 * @class AdminMenuCmp
 * @extends BaseView
 */
export default class AdminMenuCmp extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            userTab: '#userActions',
            items: {
                profile: '.profileactions-profile a',
                employees: 'profileactions-employees a',
                administration: '.administration a',
                about: '.profileactions-about a',
                logout: '.profileactions-logout a',
            },
        });
    }

    /**
     * Click item in the admin menu
     *
     * @param {string} name
     */
    public async clickItem(name: string) {
        let itemSelector = this.$('items.' + name);

        await this.driver.waitForVisible(itemSelector);
        await this.driver.moveToObject(itemSelector);
        // if we click on first item in the menu we need to wait until tooltip disappears
        await this.driver.pause(300);
        await this.driver.click(itemSelector);
    }

    /**
     *  Open admin Dropdown in the Mega Menu
     */
    public async open() {
        await this.driver.click(this.$('userTab'));
    }
}
