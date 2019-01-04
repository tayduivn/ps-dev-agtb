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

import DashletView from './dashlet-view';

/**
 * Represents Forecasts Bar Chart Dashlet in RHS pane of Forecast module
 *
 * @class ForecastsBarChartDashlet
 * @extends DashletView
 */
export default class ForecastsBarChartDashlet extends DashletView {

    protected itemSelector: String;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.forecasts-chart-wrapper',
            field: {
                selector: '.edit[field-name="{{field_name}}"] .select2-choice .select2-chosen',
            },
        });

        this.itemSelector = '.select2-result-label=';
    }

    /**
     *  Select specified item from either one of two drop-down controls in Forecast Bar Chart dashlet
     *  based on the supplied arguments
     *
     * @param {string} field_name variable to build CSS path to the drop-down
     * @param {string} val item to be select from drop-down
     * @returns {Promise<void>}
     */
    public async selectFromDropdown(field_name: string, val: string, ) {
        let element = this.$('field.selector',{field_name});
        await this.driver.click(element);
        await this.driver.waitForApp();
        await this.driver.click(`${this.itemSelector}${val}`);
        await this.driver.waitForApp();
    }
}
