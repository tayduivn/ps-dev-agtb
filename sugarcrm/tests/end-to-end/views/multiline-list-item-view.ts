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
import BaseListItemView from './list-item-view';

/**
 * @class MultilineListItemView represents multiple list view in Service Console
 * @extends BaseListItemView
 */
export default class MultilineListItemView extends BaseListItemView {

    public id: string;
    public index: number;
    public current: boolean;

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: 'tr[data-id*="{{id}}"]',
            listItem: {
                caseNum: 'td:nth-child(2)',
                caseName: 'td:nth-child(4)',
                dropdown: '.fieldset.actions.list.btn-group',
                actions: {
                    action: '.dropdown-menu a[title="{{actionName}}"]',
                }
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

        let selector = this.$('listItem.caseNum', {id: this.id});
        let rowSelector = this.$();

        await this.driver.execSync('scrollToSelector', [rowSelector]);
        await this.driver.click(selector);
        await this.driver.waitForApp();
    }

    /**
     * Select action from record actions dropdown
     *
     * @param {string} actionName represents action to select
     */
    public async chooseAction(actionName: string) {
        // Expand list item actions dropdown
        await this.openDropdown();

        // Select action from Actions dropdown
        let selector = this.$('listItem.actions.action', {actionName});
        await this.driver.click(selector);
    }

    /**
     * Open the actions dropdown
     */
    public async openDropdown() {
        // Expand list item actions dropdown
        let selector = this.$('listItem.dropdown');
        await this.driver.click(selector);
        await this.driver.waitForApp();
    }

    /**
     *  Find the position in the list of specific list item starting from the top of the list
     *
     *  @returns {number} n position of list item from the top of the list. Otherwise return -1
     */
    public async getListItemPosition() {
        for (let i = 1; i <= 20; i++) {
            // Attach ':nth-child(i)' to the tr[[data-id] element and loop until the element is found.
            // If not found within first 20 elements, return -1;
            let selector = (this.$('', {id: this.id} )).trim() + `:nth-child(${i})`;
            let isItemFound = await this.driver.isElementExist(selector);
            if (isItemFound) {
                return i;
            }
        }
        // if record is not found in the first 20 list view rows
        return -1;
    }
}
