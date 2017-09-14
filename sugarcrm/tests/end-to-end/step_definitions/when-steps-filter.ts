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
    async (value, view: FilterView) => {
        await view.setSearchField(value);
    }, {waitForApp: true});

When(/^I choose for (\w+) in (#\S+) view$/,
    async (filterName: string, view: FilterView) => {

        await view.selectFilter(filterName);

    }, {waitForApp: true});
