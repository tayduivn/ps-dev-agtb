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

import {seedbed} from '@sugarcrm/seedbed';
import BaseView from './base-view';

/**
 * @class FilterView
 * @extends BaseView
 */
export default class FilterView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.search-filter',
            searchField: '.search-name',
            filter: '.search-filter .select2-choice-type',
            activitystream: '.fa.fa-clock-o',
            listview: '.fa.fa-table'
        });

        this.globalSelectors = {
            assigned_to_me: '[data-id=assigned_to_me]',
            my_drafts: '[data-id=my_drafts]',
            favorites: '[data-id=favorites]',
            my_received: '[data-id=my_received]',
            my_sent: '[data-id=my_sent]',
            all_records: '[data-id=all_records]',
            recently_created: '[data-id=recently_created]',
            recently_viewed: '[data-id=recently_viewed]'
        };
    }

    private globalSelectors: any;

    /**
     * Set Search field name with "value"
     *
     * @param value
     * @returns {*}
     */
    public async setSearchField(value) {
        let locator = this.$('searchField');
        await this.driver.waitForVisible(locator);
        await this.driver.setValue(locator, value);
    }

    public async selectFilter(filterName: string) {
        let locator = this.$('filter');
        await this.driver.click(locator);
        await this.driver.waitForVisible(locator);
        await this.driver.click(this.globalSelectors[filterName]);
    }

    /**
     * Toggle between ListView and ActivityStream modes
     * @param {string} mode
     * @returns {Promise<void>}
     */
    public async toggleListViewMode(mode: string) {
        let locator = this.$(mode);
        await this.driver.click(locator);
    }
}
