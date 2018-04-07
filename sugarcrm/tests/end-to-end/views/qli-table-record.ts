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
export default class QliTableRecord extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            toggleLineItem: '[name=check]',
            menu: {
                editLineItem: '[name=edit_row_button]',
                deleteLineItem: '[name=delete_row_button]',

                // Edit/Delete Group
                editGroup: '[name=edit_bundle_button]',
                deleteGroup: '[name=delete_bundle_button]',

                // Add new Items to the group
                addLineItem: '[name=create_qli_button]',
                addComment: '[name=create_comment_button]',
            },
            buttons: {
                save: '.btn.inline-save',
                cancel: '.btn.inline-cancel',
                  editLineItem: '.actionmenu.list.btn-group .btn.dropdown-toggle',
                deleteLineItem: '.actionmenu.list.btn-group .btn.dropdown-toggle',

                //Open floating menu to add QLI/Comment to specific group
                addLineItem: '.btn.btn-invisible.dropdown-toggle.create-dropdown-toggle',
                 addComment: '.btn.btn-invisible.dropdown-toggle.create-dropdown-toggle',

                //Open floating menu to edit/delete group
                  editGroup: '.btn.btn-invisible.dropdown-toggle.edit-dropdown-toggle',
                deleteGroup: '.btn.btn-invisible.dropdown-toggle.edit-dropdown-toggle',

                // Inline save and cancel of QLI table line item
                  'in-line-save': '.btn.inline-save.btn-inv isible.ellipsis_inline',
                'in-line-cancel': '.btn.inline-cancel.btn-invisible.ellipsis_inline'
            },
        });
    }

    public async pressButton(buttonName) {
        await this.driver.click(this.$(`buttons.${buttonName.toLowerCase()}`));
    }

    public async toggleRecord() {
        await this.driver.click(this.$(`toggleLineItem`));
    }

    public async clickMenuItem(itemName) {
        await this.driver.click(this.$(`menu.${itemName}`));
    }

    public async openInlineMenu(itemName) {
        await this.driver.click(this.$(`buttons.${itemName}`));
    }
}
