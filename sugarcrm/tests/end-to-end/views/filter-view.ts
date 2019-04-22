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
/*
 Represents Filter view PageObject on ListView Layout.
 */

import BaseView from './base-view';

/**
 * @class FilterView represents Filter Bar on the list view
 * @extends BaseView
 */
export default class FilterView extends BaseView {

    protected fieldToUpdateItem: string;
    protected itemSelector: String;
    protected inputSelector: String;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '',
            searchField: '.search-name',
            filter: '.search-filter .select2-choice-type',
            activitystream: '.fa.fa-clock-o',
            listview: '.fa.fa-table',
            visualpipeline: '.fa-align-left',
            createButton: '.choice-filter',
            closeFilterButton: '.choice-filter-clickable .choice-filter-close',
            editFilterButton: '.choice-filter-clickable .choice-filter-label',
            filterNameField: '.filter-header input',
            actionButton: '.btn[data-action="{{dataAction}}"]',
            filterBody: {
                $: '.filter-body:nth-child({{rowNum}})',
                fieldToUpdate: 'div[data-filter="{{field}}"] .select2-container.select2',
                buttons: {
                    $: '.filter-actions.btn-group',
                    addRow: '.fa.fa-plus',
                    removeRow: '.fa.fa-minus',
                },
            },
            // Add to Target List
            filterHeader: {
                $: '.massaddtolist',
                create: 'a[name="create_button"]',
                cancel: '.cancel_button',
                update: 'a[name="update_button"]',
            },
            selectExistingList: '.filter-body.clearfix .controls .edit',
        });

        this.globalSelectors = {
            assigned_to_me: '[data-id=assigned_to_me]',
            my_drafts: '[data-id=my_drafts]',
            favorites: '[data-id=favorites]',
            my_received: '[data-id=my_received]',
            my_sent: '[data-id=my_sent]',
            all_records: '[data-id=all_records]',
            recently_created: '[data-id=recently_created]',
            recently_viewed: '[data-id=recently_viewed]',
        };

        this.itemSelector = '.select2-result=';
        this.fieldToUpdateItem = '.select2-result-label=';
        this.inputSelector = '.select2-input.select2-focused';
    }

    private globalSelectors: any;

    /**
     * Set Search field name with "value"
     *
     * @param {string} value
     * @returns {Promise<void>}
     */
    public async setSearchField(value: string) {
        let locator = this.$('searchField');
        await this.driver.waitForVisible(locator);
        await this.driver.setValue(locator, value);
    }

    /**
     * Select filter from Filter drop-down
     *
     * @param {string} filterName
     * @returns {Promise<void>}
     */
    public async selectFilter(filterName: string) {
        let locator = this.$('filter');
        await this.driver.click(locator);
        await this.driver.waitForVisible(locator);
        await this.driver.click(this.globalSelectors[filterName]);
    }

    /**
     * Select custom filter from Filter drop-down
     *
     * @param {string} customFilterName
     * @returns {Promise<void>}
     */
    public async selectCustomFilter(customFilterName: string) {
        let locator = this.$('filter');
        await this.driver.click(locator);
        await this.driver.waitForVisible(locator);
        await this.driver.click(`${this.itemSelector}${customFilterName}`);
    }

    /**
     * Toggle between ListView and ActivityStream modes
     *
     * @param {string} mode
     * @returns {Promise<void>}
     */
    public async toggleListViewMode(mode: string) {
        let locator = this.$(mode);
        await this.driver.click(locator);
    }

    /**
     * Click on Create button to create new custom filter
     *
     * @returns {Promise<void>}
     */
    public async clickCreateButton() {
        let locator = this.$('createButton');
        await this.driver.click(locator);
    }

    /**
     * Select field on which a new filter will apply
     *
     * @param {number} rowNum row number
     * @param {string} field name of the field to update
     * @param {string} pValue parent Field Value to Set
     *
     * @return {Promise<void>}
     */
    public async setFieldValue(rowNum: number, field: string, pValue: string) {
        // Set 'Parent' field value
        let selector = this.$(`filterBody.fieldToUpdate`, {rowNum, field});
        await this.driver.click(selector);
        await this.driver.waitForApp();
        await this.driver.click(`${this.fieldToUpdateItem}${pValue}`);
        await this.driver.waitForApp();
    }

    /**
     * Add new row to custom filter
     *
     * @param {number} rowNum
     * @returns {Promise<void>}
     */
    public async addRow(rowNum: number) {
        await this.driver.click(this.$(`filterBody.buttons.addRow`, {rowNum}));
        await this.driver.waitForApp();
    }

    /**
     * Delete specified row in custom filter
     *
     * @param {number} rowNum
     * @returns {Promise<void>}
     */
    public async deleteRow(rowNum: number) {
        await this.driver.click(this.$(`filterBody.buttons.removeRow`, {rowNum}));
        await this.driver.waitForApp();
    }

    /**
     * Type in the custom filter name
     *
     * @param {string} name
     * @returns {Promise<void>}
     */
    public async typeFilterName(name: string) {
        let selector = this.$('filterNameField');
        await this.driver.setValue(selector, name);
    }

    /**
     * Select action to perform on the existing custom filter
     *
     * Available actions are:
     *  cancel: 'filter-close'
     *  delete 'filter-delete'
     *  reset: 'filter-reset'
     *  save: 'filter-reset'
     *
     * @param {string} dataAction select action to perform.
     * @returns {Promise<void>}
     */
    public async performAction(dataAction: string) {
        let locator = this.$('actionButton', {dataAction});
        await this.driver.click(locator);
    }

    /**
     * Hide custom filter
     *
     * @returns {Promise<void>}
     */
    public async hideCustomFilter() {
        let locator = this.$('closeFilterButton');
        await this.driver.click(locator);
    }

    /**
     * Edit custom filter
     *
     * @returns {Promise<void>}
     */
    public async editCustomFilter() {
        let locator = this.$('editFilterButton');
        await this.driver.click(locator);
    }

    /**
     *  Check if filter row already exists
     *
     * @param {number} rowNum
     * @returns {Promise<any>}
     */
    public async isFilterRowExist(rowNum: number) {
        let selector = this.$(`filterBody`, {rowNum});
        return await this.driver.isElementExist(selector);
    }

    public async performTargetListAction(actionToPerform: string) {
        let selector = this.$(`filterHeader.${actionToPerform}`);
        await this.driver.click(selector);
    }

    /**
     * Select existing target list when adding records to Target List from the list view
     *
     * @param {string} prospectListName Target List Name
     * @returns {Promise<void>}
     */
    public async selectExistingTargetList(prospectListName: string) {

        // Click Inside relate field to expand it
        let selector = this.$(`selectExistingList`);
        await this.driver.click(selector);
        // Type existing target list name
        await this.driver.setValue(this.inputSelector, prospectListName);
        await this.driver.waitForApp();
        // Click on matching value
        await this.driver.click(`${this.fieldToUpdateItem}${prospectListName}`);
        await this.driver.waitForApp();
    }


}
