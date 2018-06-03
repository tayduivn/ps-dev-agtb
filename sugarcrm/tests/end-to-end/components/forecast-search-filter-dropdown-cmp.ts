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
 Represents Forecasts Filter Dropdown component
 */

import BaseView from '../views/base-view';

/**
 * @class ForecastSearchFilterDropdownCmp
 * @extends BaseView
 */
export default class ForecastSearchFilterDropdownCmp extends BaseView {

    protected itemSelectorsAddFilters: any;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.search-filter',
            searchField: '.filter-view.search',
            removeFilter : {
                $: '.{{itemName}}-select-choice .select2-search-choice-close',
            },
        });

        // Global Selectors
        this.itemSelectorsAddFilters = {
            include: '.select2-results .include-select-result',
            exclude: '.select2-results .exclude-select-result',
        };
    }

    /**
     * Add Filter
     *
     * @param value
     * @returns {Promise<void>}
     */
    public async addFilter(value) {
        await this.driver.click(this.$('searchField'));
        await this.driver.waitForApp();
        await this.driver.click(this.itemSelectorsAddFilters[value]);
        await this.driver.click('body');
    }

    /**
     * Remove Filter
     *
     * @param value
     * @returns {Promise<void>}
     */
    public async removeFilter(value) {

        if (this.driver.isVisible(this.$(`removeFilter`, {itemName: value}) ) ) {
            await this.driver.click(this.$(`removeFilter`, {itemName: value}) );
        }
        await this.driver.click('body');
    }
}
