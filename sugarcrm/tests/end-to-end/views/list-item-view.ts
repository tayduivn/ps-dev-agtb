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
import {seedbed} from '@sugarcrm/seedbed';

/**
 * @class ListItemView
 * @extends BaseView
 */
export default class ListItemView extends BaseView {

    public id: string;
    public index: number;
    public current: boolean;

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: 'tr[name*="{{id}}"]',
            listItem: {
                listItemName: 'a[href*="{{id}}"]',
                listItemGrip: '.menu-container-grip',
                buttons: {
                    preview: '.fa.fa-eye',
                    dropdown: 'a.btn.dropdown-toggle',
                    edit: '[name="edit_button"]',
                    save: '[name="inline-save"]',
                    cancel: '[name="inline-cancel"]',
                }
            },
            buttons: {
                addRow: '.addBtn'
            }
        });

        this.id = options.id;
        this.index = options.index;
        this.current = !this.id && !this.index;
    }

    /**
     * Click on list view item list element (name in most cases)
     *
     * @returns {*}
     */
    public async clickListItem() {

        let selector = this.$('listItem.listItemName', {id: this.id});
        let rowSelector = this.$();

        return seedbed.client
            .execSync('scrollToSelector', [rowSelector])
            .click(selector);
    }

    public async clickPreviewButton() {

        let selector = this.$('listItem.buttons.preview', {id: this.id});
        let rowSelector = this.$();

        return seedbed.client
            .execSync('scrollToSelector', [rowSelector])
            .click(selector);
    }

    /**
     * Checks if button is visible
     *
     * @param itemName
     * @returns {Promise<Client<boolean>>}
     */
    public async isVisible(itemName) {
        return seedbed.client.isVisible(this.$('listItem.buttons.' + itemName.toLowerCase()));
    }

    /**
     * Click on list view button
     *
     * @param buttonName
     * @returns {Promise<SeedbedClient<void>>}
     */
    public async clickListButton(buttonName) {
        let selector = this.$('listItem.buttons.' + buttonName.toLowerCase(), {id: this.id});
        let rowSelector = this.$();

        return seedbed.client
            .execSync('scrollToSelector', [rowSelector])
            .click(selector);
    }

    /**
     * Open the actions dropdown
     *
     * @returns {Promise<void>}
     */
    public async openDropdown() {
        await this.clickListButton('dropdown');
    }

}
