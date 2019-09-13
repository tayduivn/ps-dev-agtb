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

export default class LeadConversionView extends BaseView {

    constructor(options) {
        super(options);

        const {module} = options;

        this.selectors = this.mergeSelectors({
            $: `.accordion-group [data-module=${this.module}]`,
            header: {
                button: {
                    createrecord: 'a[name="associate_button"]',
                    reset: 'a[name="reset_button"]',
                    chevrondown: '.fa.fa-chevron-down',
                    chevronup: '.fa.fa-chevron-up',
                    selectrecord: 'a[name="associate_button"]',
                },
            },
            searchbar: '.search-name',
        });
    }

    /**
     * Click on filter search bar
     *
     * @returns {Promise<void>}
     */
    public async searchBarClick(): Promise<any> {
        let selector = this.$('searchbar');
        await this.driver.click(selector);
    }

    public async btnClick(btnName): Promise<any> {
        let selector = this.$('header.button.' + btnName);
        await this.driver.click(selector);
    }

    public async toggleAccordion(action): Promise<any> {
        let selector = this.$('header');
        if (await this.driver.isVisible(selector) ) {
            await this.driver.click(selector);
        }
    }
}
