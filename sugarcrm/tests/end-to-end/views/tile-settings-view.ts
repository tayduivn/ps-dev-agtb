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
            }
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
     * @param value
     * @returns {Promise<void>}
     */
    public async selectValueFromDropdown(moduleName, index, i, val) {

        let  selector = this.$('moduleSettings.fields.dropdown', {moduleName, index, i});
        await this.driver.click(selector);
        await this.driver.waitForApp();
        await this.driver.click(`${this.itemSelector}${val}`);
        await this.driver.waitForApp();
    }
}
