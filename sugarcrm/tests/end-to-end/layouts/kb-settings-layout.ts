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

import {BaseView, seedbed} from '@sugarcrm/seedbed';
import ListLayout from './list-layout';

/**
 * Represents searchAndAdd layout.
 *
 * @class KBSettingsLayout
 * @extends BaseView
 */
export default class KBSettingsLayout extends ListLayout {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.drawer.active',
            rows: {
                $: '.control-group:nth-child({{index}})',
                firstrow: '.first-row',
                controls: {
                    code: 'input[name="key_languages"]',
                    label: 'input[name="value_languages"]',
                    favorite: '.btn.third',
                    removeItem: '.btn.second',
                    addItem: '.btn.first',
                },
            }
        });

        this.type = 'drawer';
    }

    /**
     * Add new language to the supported languages in KB Settings drawer
     *
     * @param {string} languageCode
     * @param {string} languageValue
     * @param {string} primary
     * @param {number} index
     * @returns {Promise<void>}
     */
    public async addSupportedLanguage(languageCode: string, languageValue: string, primary: string, index: number) {

        let selector = this.$(`rows.controls.addItem`,{index: index});
        await this.driver.click(selector);
        await this.driver.waitForApp();

        selector = this.$(`rows.controls.code`,{index: index+1});
        await this.driver.setValue(selector, languageCode);
        await this.driver.waitForApp();

        selector = this.$(`rows.controls.label`,{index: index+1});
        await this.driver.setValue(selector, languageValue);
        await this.driver.waitForApp();

        if (primary === 'true') {
            selector = this.$(`rows.controls.favorite`, {index: index+1});
            await this.driver.click(selector);
            await this.driver.waitForApp();
        }
    }

    /**
     *  Check if + button (Add Item button) is visible
     *
     * @param {number} index
     * @returns {Promise<any>}
     */
    public async isButtonVisible(index: number) {
        let selector = this.$(`rows.controls.addItem`,{index: index});
        return this.driver.isVisible(selector);
    }

    /**
     * Remove selected language
     *
     * @param index
     * @returns {Promise<void>}
     */
    public async removeSupportedLanguage(index: number) {
        let selector = this.$(`rows.controls.removeItem`, {index: index});
        await this.driver.click(selector);
    }
}
