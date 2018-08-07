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

import BaseView from './base-view';

/**
 * Represents Dashboard view.
 *
 * @class DashletView
 * @extends BaseView
 */
export default class DashletView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.dashlet-cell',
                header: '.dashlet-header',
                buttons: {
                    cog: '.btn.btn-invisible.dropdown-toggle',
                },
                menuItems:{
                    edit: 'a[name="edit_button"]',
                    refresh:'a[name="refresh_button"]',
                    remove: 'a[name="remove_button"]',
                },

            content: 'dashlet-content'
        });
    }

    /**
     * Perform standard dashlet operations such as edit, refresh or remove
     * Note: Those operations are only available to Admin
     *
     * @param action
     * @returns {Promise<void>}
     */
    public async performAction(action) {
        await this.driver.click(this.$(`buttons.cog`));
        await this.driver.click(this.$(`menuItems.`+ action));
    }
}
