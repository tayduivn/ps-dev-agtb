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

/**
 * Represents Quick Create Menu
 *
 * @class QuickCreateMenuCmp
 * @extends BaseView
 */
export default class QuickCreateMenuCmp extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            caret: '.fa.fa-plus.fa-md',
            menuitem: '.quickcreate .actionLink[data-module={{name}}]',
        });
    }

    /**
     * Select menu item under the Quick Create dropdown menu
     *
     * @param {string} name
     */
    public async clickItem(name: string) {
        let itemSelector = this.$('menuitem', {name} );
        await this.driver.waitForVisible(itemSelector);
        await this.driver.moveToObject(itemSelector);
        await this.driver.click(itemSelector);
    }

    /**
     *  Open Quick Create dropdown menu by clicking on "+" (plus) button in Mega Menu
     */
    public async open() {
        await this.driver.click(this.$('caret'));
    }
}
