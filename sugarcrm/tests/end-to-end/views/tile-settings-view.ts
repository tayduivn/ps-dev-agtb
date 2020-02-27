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
                $: '[data-modulename={{moduleName}}]',
                // Source tile to move
                source: '[data-columnname={{sourceList}}] li[data-headervalue="{{tileToBeMoved}}"]',
                // Empty destination list
                toEmptyList: '[data-columnname={{destinationList}}]',
                // Destination list with at least one item present
                to: '[data-columnname={{destinationList}}] li:nth-child({{position}})',
            },
            // field pillar in the tile body
            field: '.pipeline-fields#{{moduleName}} .select2-choices.ui-sortable li:nth-child({{i}})',
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
     * Drag-n-drop tile block between 'Available Values' and 'Hidden Values' lists or
     * re-arrange tiles in Tile View Settings > Header values section
     *
     * @param {string} moduleName
     * @param {string} tileToBeMoved tile to move
     * @param {string} destinationList
     * @param {string} position to move the tile block to in Tile View settings
     */
    public async moveItem(moduleName: string, tileToBeMoved: string, destinationList: string, position:string): Promise<void> {

        // Initialize source list and build selectorSource
        let sourceList = 'white_list';
        let selectorSource = this.$('move.source', {moduleName, tileToBeMoved, sourceList});

        // Check if such selector source exists and if not, correct the 'from_list' and rebuild selectorSource
        let flag: boolean = await this.driver.isElementExist(selectorSource);
        if (!flag) {
            sourceList = 'black_list';
            selectorSource = this.$('move.source', {moduleName, tileToBeMoved, sourceList});
        }

        // Position equals to zero represents empty list ( list with no items added to it)
        let pathToElement = (position !== "0") ? 'move.to' : 'move.toEmptyList';

        // Construct the destination selector
        let selectorTo = this.$(pathToElement, {moduleName, destinationList, position});

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
                if ( sourceList === destinationList ) {
                    selectorTo = selectorTo + ' span';
                }

                // Prform move
                await driver.moveToObject(selectorTo);

                // In case of source and destination lists are the same calculate yOffset based on the index
                // Otherwise, set zero  offset
                if ( sourceList === destinationList ) {
                    let originalColumnPosition = await this.currentColumnIndex(moduleName, tileToBeMoved, sourceList);
                    if (originalColumnPosition != -1 ) {
                        let yOffset = (originalColumnPosition > Number(position)) ? -15 : 15;
                        await driver.moveTo(null, 30, yOffset);
                    } else {
                        throw new Error(`The position of item ${tileToBeMoved} could not be found in the '${sourceList} 'list.`);
                    }
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
     * @param {string} moduleName
     * @param {string} tileToBeMoved
     * @param {string} sourceList - original list to move item from
     * @return {number}
     */
    private async currentColumnIndex(moduleName: string, tileToBeMoved: string, sourceList: string): Promise<number> {

        for (let curIndex: number = 1; curIndex < 25; curIndex++) {
            let selectorSource = this.$('move.source', {moduleName, tileToBeMoved, sourceList}) + `:nth-child(${curIndex})`;
            if ( await this.driver.isElementExist(selectorSource)) {
                return curIndex;
            }
        }
        return -1;
    }

    /**
     * Remove field from tile body
     *
     * @param {string} moduleName name of the tab in Tile View config
     * @param {string} fieldName name of the field to be removed from tile body
     * @return {boolean}
     */
    public async removeFieldFromTileBody(moduleName: string, fieldName: string): Promise<boolean> {

        for (let i=1; i<7; i++) {
            let selector = this.$('field', {moduleName, i});
            if (await this.driver.isElementExist(selector) ) {

                let field = await this.driver.getText(selector + ' div');
                if (field === fieldName) {
                    await this.driver.click(selector + ' a');
                    return true;
                }
            } else {
                throw new Error ('Error. Specified path does not exists');
            }
        }
        return false;
    }
}
