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

import {When} from '@sugarcrm/seedbed';
import FilterView from '../views/filter-view';

/**
 * Search for "value" in list view search filter
 *
 * @example "I search for "Account_Search" in #AccountsList:FilterView view"
 */
When(/^I search for "([^"]*)" in (#\S+) view$/,
    async function(value: string, view: FilterView) {
        await view.setSearchField(value);

        // need to handle setTimeout 400ms in search box
        await this.driver.pause(500);

    }, {waitForApp: true});

When(/^I choose for (\w+) in (#\S+) view$/,
    async function(filterName: string, view: FilterView) {

        await view.selectFilter(filterName);

    }, {waitForApp: true});

/**
 * Toggle between List VIew, Tile View and Activity Stream views
 *
 * @example When I select ActivityStream in #ContactsList.FilterView
 */
When(/^I select (ActivityStream|ListView|TileView) in (#\S+)$/,
    async function (mode: string, view: FilterView) {

        await view.toggleListViewMode(mode.toLowerCase());
    }, {waitForApp: true});
