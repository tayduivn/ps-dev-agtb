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
import ForecastFilterView from "../views/forecast-filter-view";

/**
 * Add filter in Forecast Sales Rep worksheet
 */
When(/^I add "([^"]*)" in (#\S+) view$/,
    async function(value: string, view: ForecastFilterView) {
        await view.forecastSearchFilterDropdownCmp.addFilter(value.toLowerCase());

        // need to handle setTimeout 400ms in search box
        await this.driver.pause(500);

    }, {waitForApp: true});

/**
 *  Remove filter in Forecast Sales Rep worksheet
 */
When(/^I remove "([^"]*)" filter in (#\S+) view$/,
    async function(value: string, view: ForecastFilterView) {
        await view.forecastSearchFilterDropdownCmp.removeFilter(value.toLowerCase());
    }, {waitForApp: true});
