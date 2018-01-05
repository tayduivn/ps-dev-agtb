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
 * Represents Record view.
 *
 * @class QliRecord
 * @extends BaseView
 */
export default class QliRecord extends BaseView {

    public id: string;

    constructor(options) {
        super(options);

        this.id = options.id || '';
        this.module = 'Products';

        this.selectors = this.mergeSelectors({
            $: `[record-id="${this.id}"]`,
            buttons: {
                save: '.btn.inline-save',
                cancel: '.btn.inline-cancel',
                QliMenu: '.actionmenu.list.btn-group .btn.dropdown-toggle',
                'in-line-save': '.btn.inline-save.btn-invisible.ellipsis_inline',
                'in-line-cancel': '.btn.inline-cancel.btn-invisible.ellipsis_inline'
            },
            menu: {
                editLineItem: '[name=edit_row_button]',
                deleteLineItem: '[name=delete_row_button]',
            }
        });
    }

    public async pressButton(buttonName) {
        await this.driver.click(this.$(`buttons.${buttonName.toLowerCase()}`));
    }

    public async openLineItemMenu() {
        await this.driver.click(this.$('buttons.QliMenu'));
    }

    public async clickMenuItem(itemName) {
        await this.driver.click(this.$(`menu.${itemName}`));
    }
}
