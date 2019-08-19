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

import RecordLayout from '../layouts/record-layout';
import DrawerLayout from '../layouts/drawer-layout';

/**
 * Represents Configuration screen of dashable record dashlet
 *
 * @class DashableRecordDashletConfig
 * @extends RecordLayout
 */
export default class DashableRecordDashletConfig extends DrawerLayout {

    private matchInput: String;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.drawer.active',
            tab: '.dashlet-tabs-row [data-module-name="{{tabName}}"]',
            field: {
                $: '.select2-search-field',
                input: 'input',
            }
        });

        this.matchInput = '.select2-highlighted';
    }

    /**
     * Select tab by name
     *
     * @param {string} tabName
     */
    public async navigateToTab(tabName: string): Promise<void> {
        let selector  = this.$('tab', {tabName});
        await this.driver.click(selector);
    }

    /**
     * Close the pill if found and return 'true'. Otherwise return 'false'.
     *
     * @param {string} tabName
     * @return {boolean}
     */
    public async closePill(tabName: string) {
        // XPATH is used here because there is no unique CSS selector available in this case
        let pillarCloseBtnSelector = `//li[@class="select2-search-choice"]/div[text()="${tabName}"]/following-sibling::a`;
        if (this.driver.isElementExist(pillarCloseBtnSelector)) {
            await this.driver.click(pillarCloseBtnSelector);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add tab to dashable record dashlet
     *
     * @param {string} val module name
     */
    public async addTab(val: string) {
        await this.driver.click(this.$('field'));
        await this.driver.setValue(this.$('field.input'), val);
        await this.driver.waitForApp();
        await this.driver.click(`${this.matchInput}`);
    }
}
