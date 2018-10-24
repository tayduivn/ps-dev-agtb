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
 * @class PmseEtComposeVarbookList
 * @extends BaseView
 */
export default class PmseEtComposeVarbookList extends BaseView {
    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '',
            buttons: {
                'module_dropdown': '.select2-container.select2.search-filter a',
                'module_dropdown_option': '.select2-container.select2.search-filter a',
            },
            moduleDropdown: {
                $: '#select2-drop ul {{elementSelector}}',
            },
            tableRow: {
                $: 'tr[name="{{rowName}}"]',
                dropdown: 'td[data-column="process_et_field_type"]',
            },
            activeDropdown: {
                $: '.select2-drop.select2-display-none.select2-drop-active ul {{elementSelector}}',
            },
            tableLinkRecord: {
                $: '.table.table-striped.dataTable tbody {{elementSelector}}',
            },
        });
    }

    /**
     * Expands the dropdown to choose a target module and selects one of the options to refresh the table
     * with the corresponding values.
     *
     * @param string moduleText
     * @returns {Promise<void>}
     */
    public async clickModuleSelect(moduleText) {
        let elementText;
        let elementSelector = 'li';
        let elements = await this.driver.elements(this.$(`moduleDropdown`, {elementSelector}));
        let countOfLiElements = elements.value.length;

        for (let x = 1; x <= countOfLiElements; x++) {
            elementSelector = 'li:nth-child(' + x + ')';
            elementText = await this.driver.getText(this.$(`moduleDropdown`, {elementSelector}));
            if (moduleText == elementText) {
                await this.driver.click(this.$(`moduleDropdown`, {elementSelector}));
                await this.driver.waitForApp();
                break;
            }
        }
    }

    /**
     * Finds the row with the field to be selected clicks the dropdown in the first column
     * and chooses an option.
     *
     * @param string moduleName
     * @param string fieldName
     * @param string dropdownOption
     * @returns {Promise<void>}
     */
    public async selectFieldOption(moduleName, fieldName, dropdownOption) {
        let elementText;
        let elements;
        let countOfLiElements;
        let elementSelector = 'li';
        let rowName = moduleName + '_' + fieldName;

        await this.driver.click(this.$(`tableRow.dropdown`, {rowName}));
        await this.driver.waitForApp();

        elements = await this.driver.elements(this.$(`activeDropdown`, {elementSelector}));
        countOfLiElements = elements.value.length;

        for (let x = 1; x <= countOfLiElements; x++) {
            elementSelector = 'li:nth-child(' + x + ')';
            elementText = await this.driver.getText(this.$(`activeDropdown`, {elementSelector}));
            if (dropdownOption == elementText) {
                await this.driver.click(this.$(`activeDropdown`, {elementSelector}));
                await this.driver.waitForApp();
                break;
            }
        }
    }

    /**
     * Finds the module from the table and clicks the radio button.
     *
     * @param string moduleName
     * @returns {Promise<void>}
     */
    public async selectRecordLink(moduleName) {
        let element;
        let elementSelector = 'tr';
        let elements = await this.driver.elements(this.$(`tableLinkRecord`, {elementSelector}));
        let countOfLiElements = elements.value.length;

        for (let x = 1; x <= countOfLiElements; x++) {
            elementSelector = 'tr:nth-child(' + x + ') td:nth-child(2)';
            element = await this.driver.getText(this.$(`tableLinkRecord`, {elementSelector}));
            if (element == moduleName) {
                elementSelector = 'tr:nth-child(' + x + ') td:nth-child(1)';
                await this.driver.click(this.$(`tableLinkRecord`, {elementSelector}));
                await this.driver.waitForApp();
                break;
            }
        }
    }

}
