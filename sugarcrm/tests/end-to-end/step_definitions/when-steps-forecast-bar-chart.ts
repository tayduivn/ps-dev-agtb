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
import ForecastsBarChartDashlet from '../views/forecasts-bar-chart-dashlet';

/**
 * Select item from dataset drop-down in Forecast Bar Chart dashlet
 *
 * @example "When I select Best in #Dashboard.ForecastsBarChartDashlet"
 */
When(/^I select (Likely|Best|Worst) in (#\S+)$/,
    async function (itemToSelect: string, view: ForecastsBarChartDashlet): Promise<void> {
        await view.selectFromDropdown('dataset', itemToSelect);
    }, {waitForApp: true});

/**
 * Select item from group-by drop-down in Forecast Bar Chart dashlet
 *
 * @example "When I select Sales Stage in #Dashboard.ForecastsBarChartDashlet"
 */
When(/^I select (In Forecast|Sales Stage|Probability) in (#\S+)$/,
    async function (itemToSelect: string, view: ForecastsBarChartDashlet): Promise<void> {
        await view.selectFromDropdown('group_by', itemToSelect);
    }, {waitForApp: true});
