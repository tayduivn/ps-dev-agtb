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

import BaseListView from './baselist-view';
import DrawerLayout from '../layouts/drawer-layout';

/**
 * Represents Pipeline View
 *
 * @class TileViewSettings
 * @extends BaseListView
 */
export default class TileViewSettings extends DrawerLayout {

    protected itemSelectorsModules: any;
    protected itemSelector: string;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            generalSettings: {
                $: '.pipeline-modules-group',
                module: 'li[data-module={{moduleName}}] a',
                modulesList: '.accordion-inner .edit'

            },
            moduleSettings: {
                $: '.config-visual-pipeline-group',
                moduleTab: '.tab[aria-controls={{moduleName}}]',
                fields: {
                    $: '.pipeline-fields#{{moduleName}} .row-fluid:nth-child({{index}})',
                    dropdown: '.record-cell:nth-child({{i}}) .select2-container.select2.required',
                }
            },
            move: {
                // Source tile to move
                source: '[data-columnname={{from_list}}] li[data-headervalue="{{source}}"]',
                // Empty destination list
                toEmptyList: '[data-modulename={{moduleName}}] [data-columnname={{to_list}}]',
                // Destination list with at least one item present
                to: '[data-modulename={{moduleName}}] [data-columnname={{to_list}}] li:nth-child({{index}})',
            },
        });

        // Global Selectors
        this.itemSelectorsModules = {
            Cases: '.enabled-module-result-item[data-module=Cases]',
            Tasks: '.enabled-module-result-item[data-module=Tasks]',
            Opportunities: '.enabled-module-result-item[data-module=Opportunities]',
        };

        this.itemSelector = '.select2-result-label=';

    }

    /**
     * Remove specified module from the list of Tile View supported modules in General Settings accordion
     *
     * @param moduleName
     * @returns {Promise<void>}
     */
    public async hideModule(moduleName) {
        let selector = this.$('generalSettings.module', {moduleName} );
        await this.driver.click(selector);
    }

    /**
     *  Select specified module for Tile View support in General Settings accordion
     *
     * @param moduleName
     * @returns {Promise<void>}
     */
    public async showModule(moduleName) {
        // Click Modules List control to open dropdown
        let selector = this.$('generalSettings.modulesList');
        await this.driver.click(selector);
        await this.driver.waitForApp();
        // Select specified module from Modules List dropdown
        await this.driver.click(this.itemSelectorsModules[moduleName]);
        await this.driver.waitForApp();
    }

    /**
     * Select specified module tab in Module Settings accordion
     *
     * @param moduleName
     * @returns {Promise<void>}
     */
    public async switchTab(moduleName) {
        let selector = this.$('moduleSettings.moduleTab', {moduleName} );
        await this.driver.click(selector);
    }

    /**
     * Select item from dropdown in Tile View Settings
     *
     * @param moduleName
     * @param index
     * @param i
     * @param val
     * @returns {Promise<void>}
     */
    public async selectValueFromDropdown(moduleName, index, i, val) {

        let  selector = this.$('moduleSettings.fields.dropdown', {moduleName, index, i});
        await this.driver.click(selector);
        await this.driver.waitForApp();
        await this.driver.click(`${this.itemSelector}${val}`);
        await this.driver.waitForApp();
    }

    /**
     * Drag-n-drop tile block between 'Available Values' and 'Hidden Values' lists in Tile View Settings > Header values section
     *
     * @param {string} moduleName
     * @param {string} source item to move
     * @param {string} to_list
     * @param {string} index index of the position to move the tile block to in Tile View settings
      */
    public async moveItem(moduleName: string, source: string, to_list: string, index:string): Promise<void> {


        // Initialize source list and build selectorSource
        let from_list = 'white_list';
        let selectorSource = this.$('move.source', {source, from_list});

        // Check if such selector source exists and if not, correct the 'from_list' and rebuild selectorSource
        let flag: boolean = await this.driver.isElementExist(selectorSource);
        if (!flag) {
            from_list = 'black_list';
            selectorSource = this.$('move.source', {source, from_list});
        }

        // Index equals to zero represents the case when black or white list is empty and does not have any tiles yet
        let pathToElement = (index !== "0") ? 'move.to' : 'move.toEmptyList';

        // Construct the destination selector
        let selectorTo = this.$(pathToElement, {moduleName, to_list, index});

        // Perform the move
        if (await this.driver.isElementExist(selectorSource) &&
            await this.driver.isElementExist(selectorTo)) {
            try {
                let driver = this.driver;
                await driver.moveToObject(selectorSource);
                await driver.moveTo(null, 0, 0);
                await driver.pause(1000);
                await driver.buttonDown(0);
                await driver.pause(1000);

                // In case of source and destination lists are the same add span to CSS
                if ( from_list === to_list ) {
                    selectorTo = selectorTo + ' span';
                }

                // Prform move
                await driver.moveToObject(selectorTo);

                // In case of source and destination lists are the same calculate yOffset based on the index
                // Otherwise, set zero  offset
                if ( from_list === to_list ) {
                    let curColumnIndex = await this.currentColumnIndex(source, from_list);
                    let yOffset = (curColumnIndex > Number(index)) ? -15 : 15;
                    await driver.moveTo(null, 30, yOffset);
                } else {
                    await driver.moveTo(null, 0, 0);
                }
                await driver.pause(1000);
                await driver.buttonUp(0);
                await driver.pause(1000);
            } catch (e) {
                throw new Error("Error... Something went wrong while performing drag-n-drop!");
            }
        } else {
            throw new Error('Either source or destination element could not be found on the page...');
        }
    }

    /**
     * Calculate index of the source item. Return index in the list if item is found. Otherwise return -1
     *
     * @param {string} source - item name
     * @param {string} from_list - original list to move item from
     * @return {number}
     */
    private async currentColumnIndex(source: string, from_list: string): Promise<number> {

        for (let curIndex: number = 1; curIndex < 25; curIndex++) {
            let selectorSource = this.$('move.source', {source, from_list}) + `:nth-child(${curIndex})`;
            if ( await this.driver.isElementExist(selectorSource)) {
                return curIndex;
            }
        }
        return -1;
    }
}
