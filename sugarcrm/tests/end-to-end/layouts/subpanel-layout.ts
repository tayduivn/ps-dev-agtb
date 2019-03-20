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
import ListView from '../views/list-view';

/**
 * Represents subpanel layout.
 *
 * @class SubpanelLayout
 * @extends BaseView
 */
export default class SubpanelLayout extends ListView {
    public link: string;

    /**
     * Create a new subpanel layout.
     * @param {Object} options Component options.
     * @param {string} options.link Name of the link used to join this subpanel
     *   to the related record.
     */
    constructor(options) {
        super(options);

        this.link = options.link;

        this.selectors = this.mergeSelectors({
            $: '.filtered.tabbable.tabs-left[data-subpanel-link=' + this.link + ']',
            header: {
                $: '.subpanel-header',
                buttons: {
                    plusButton: '.subpanel-controls .fa.fa-plus',
                    toggleMenuButton: '.subpanel-controls .fa.fa-caret-down',
                },
                /* Index is needed for 'Select From Reports' subpanel action in Target List:
                 1: - Link Existing Record
                 2: - Select from Reports 
                */
                selectSubpanelAction: '.subpanel-controls li:nth-child({{index}}) a',

            },
            massupdate: {
                toggleMassUpdate: '.fieldset.actions.actionmenu.list.btn-group .btn.dropdown-toggle',
                GenerateQuote: '.dropdown-menu a[name="quote_button"]',
                Delete: '.dropdown-menu a[name="massdelete_button"]',
            },
            footer: {
                $: '.block-footer'
            },
        });
    }

    /**
     * Open this subpanel.
     * @return {Promise<any>} Result of opening this subpanel.
     */
    public async open(): Promise<any> {
        let selector = this.$();
        await this.driver
            .execSync('scrollToSelector', [selector])
            .click(selector);
    }

    /**
     * Click sub-panel Plus button.
     */
    public async createRecord(): Promise<any> {
        let selector = this.$('header.buttons.plusButton');
        await this.driver
            .execSync('scrollToSelector', [selector])
            .click(selector);
    }

    public async openActionsMenu(): Promise<any> {
        let selector = this.$('header.buttons.toggleMenuButton');

        await this.driver.scroll(selector);

        await this.driver.click(selector);
    }

    public async selectMenuItem(index): Promise<any> {
        let selector = this.$(`header.selectSubpanelAction`, {index});
        await this.driver.scroll(selector);
        await this.driver.click(selector);
    }

    public async toggleSubpanelMassUpdate(): Promise<any> {
        let selector = this.$('massupdate.toggleMassUpdate');

        await this.driver
            .click(selector);
    }

    public async clickMenuItem(menuItemName): Promise<any> {

        await this.toggleSubpanelMassUpdate();
        let selector = this.$('massupdate.' + menuItemName);

        await this.driver
            .click(selector);
    }
}
