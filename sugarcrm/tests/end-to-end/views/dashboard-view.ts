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
                newrow: '.row-fluid[name="dashlet_last_{{index}}0"] .add-row.empty',
                adddashlet: '.add-dashlet .fa.fa-plus',
            },
            elements: {
                HomeDashboard: '',
                dashlet: '.dashlets.row-fluid',
                FirstDashlet: '.row-fluid[name="dashlet_00"]',
                SecondDashlet: '.row-fluid[name="dashlet_01"]',
                ServiceConsoleOverview: '.agent_workbench_dashboard',

                // 3x3 dashboard like Overview tab is Service Console
                first_row_left_dashlet: '[name=dashlet_000]',
                first_row_middle_dashlet: '[name=dashlet_001]',
                first_row_right_dashlet: '[name=dashlet_002]',
                second_row_left_dashlet: '[name=dashlet_010]',
                second_row_middle_dashlet: '[name=dashlet_011]',
                second_row_right_dashlet: '[name=dashlet_012]',
                third_row_left_dashlet: '[name=dashlet_020]',
                third_row_middle_dashlet: '[name=dashlet_021]',
                third_row_right_dashlet: '[name=dashlet_022]',

                // 2x2 dashboard like Overview tab of Sales Renewal console
                dashboard2by2_top_right: '[name=dashlet_100]',
                dashboard2by2_bottom_right: {
                    $: '[name=dashlet_110]',
                    chart: '.sc-bubble-wrap',
                },
            }
        });
    }

    /**
     * Click + button on the specified column when adding dashlet to the dashboard
     *
     * @param {string} buttonName
     * @param {number} index
     * @returns {Promise<void>}
     */
    public async clickPlusButton(buttonName: string, index: number) {
        let selector = this.$(`buttons.newrow`, {index});
        await this.driver.click(selector);
    }

}
