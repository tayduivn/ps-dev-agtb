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

import DrawerLayout from '../layouts/drawer-layout';

/**
 * Represents List page layout.
 *
 * @class ConsoleSettingsConfig
 * @extends DrawerLayout
 */
export default class ConsoleSettingsConfig extends DrawerLayout {

    protected itemSelector: string;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '#config-accordion',
            // Navigation between tabs
            tabs: {
                $: '.nav-tabs',
                tab: '[aria-controls="{{tabName}}"]',
                activeTab: '.tab.ui-tabs-active[aria-controls="{{tabName}}"]',
            },
            // Field specific selector
            fields: {
                $: 'div[id={{tabName}}] [field-name=order_by_{{sortingOrderField}}_group]',
                field: '[field-name=order_by_{{sortingOrderField}}]',
                clearField: '.select2-search-choice-close',
                direction: '[name={{sortingDirection}}]',
            },
            // Basic filter support
            filter: {
                $: 'div[id={{tabName}}]',
                filterLine: '.filter-body.console-config:nth-child({{index}}) .edit',
            },
            // restore default settings
            restoreDefault: 'div[id={{tabName}}] .restore-defaults-btn',
        });

        this.itemSelector = '.select2-result-label=';
    }

    /**
     * Navigate to specified tab in Console Settings drawer
     *
     * @param {string} tabName
     */
    public async navigateToTab(tabName: string) {
        // Only click tab if it is not already active
        let isSelected = await this.driver.isElementExist(this.$('tabs.activeTab', {tabName}));
        if (!isSelected) {
            let selector = await this.$('tabs.tab', {tabName});
            await this.driver.click(selector);
        }
    }

    /**
     * Set value in Primary or Secondary sort order dropdown fields and
     * specify sorting direction
     *
     * @param tabName name of the currently opened tab in Console Settings configuration drawer
     * @param val value to set in sorting order field
     * @param sortingOrderField primary or secondary sorting order field
     * @param sortingDirection ascending or descending sorting direction
     */
    public async setSortCriteria(tabName: string, val: string, sortingOrderField: string, sortingDirection: string ):Promise <void> {

        let  selector = await this.$('fields.field', {tabName, sortingOrderField});
        // set sorting order field
        if ( await this.driver.isElementExist(selector) ) {
            await this.driver.click(selector);
            await this.driver.waitForApp();
            await this.driver.click(`${this.itemSelector}${val}`);
            await this.driver.waitForApp();
        }

        // set sort direction
        selector = await this.$('fields.direction', {tabName, sortingOrderField, sortingDirection});
        if ( await this.driver.isElementExist(selector) ) {
            await this.driver.click(selector);
            await this.driver.waitForApp();
        }
    }

    /**
     * Clear sorting criteria by click 'x' button in dropdown control
     *
     * @param tabName name of the currently opened tab in Console Settings configuration drawer
     * @param sortingOrderField primary or secondary sorting order field
     */
    public async clearSortCriteria(tabName: string, sortingOrderField: string ):Promise <void> {
        // try to clear the field in case the css path to specified menu item in sorting order dropdown is not found
        await this.driver.click(this.$('fields.clearField', {tabName, sortingOrderField}));
    }

    /**
     *  Set basic filter value line 'My Items' or 'My Favorites'
     *
     * @param tabName name of the currently opened tab in Console Settings configuration drawer
     * @param val sorting order value
     */
    public async setFilter(tabName: string, val: string):Promise <void> {

        // The 'My Items' filter is in a second filter line be default
        // in Opportunities and Cases tabs of Console Settings
        let index = (tabName === 'Opportunities' || tabName === 'Cases' ) ? "2" : "1";
        let  selector = this.$('filter.filterLine', {tabName, index} );

        await this.driver.click(selector);
        await this.driver.waitForApp();
        await this.driver.click(`${this.itemSelector}${val}`);
        await this.driver.waitForApp();
    }

    /**
     * Restore default settings in the current tab
     *
     * @param tabName name of the currently opened tab in Console Settings configuration drawer
     */
    public async restoreDefault(tabName: string):Promise <void> {
        let selector = this.$('restoreDefault', {tabName});
        if ( await this.driver.isElementExist(selector) ) {
            await this.driver.click(selector);
        }
    }
}
