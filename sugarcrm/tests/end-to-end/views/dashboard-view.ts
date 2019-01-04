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

/**
 * Represents Dashboard view.
 *
 * @class DashboardView
 * @extends BaseView
 */
export default class DashboardView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.dashboard',
            buttons: {
                newrow: '.add-row.empty',
                adddashlet: '.add-dashlet .fa.fa-plus'
            },
            elements: {
                dashlet: '.dashlets.row-fluid',
                InForecastDashlet: '.row-fluid[name="dashlet_00"]',
                ForecastBarChart: '.row-fluid[name="dashlet_01"]',
            }
        });
    }
}
