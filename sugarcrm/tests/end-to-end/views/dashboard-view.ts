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
                adddashlet: '.empty-grid',
            },

            // Service and Renewals console
            tab: 'a[data-original-title="{{tabName}}"]',
            closeSideDrawer: '.close-drawer',

            elements: {
                HomeDashboard: '',
                dashlet: '.dashlets.row-fluid',
                FirstDashlet: '.dashlet-container[name="dashlet_0"]',
                SecondDashlet: '.dashlet-container[name="dashlet_1"]',
                ServiceConsoleOverview: '.agent_workbench_dashboard',

                // Default Service Console
                first_row_left_dashlet: '[name=dashlet_0]',
                first_row_middle_dashlet: '[name=dashlet_1]',
                first_row_right_dashlet: '[name=dashlet_2]',
                second_row_left_dashlet: '[name=dashlet_3]',
                second_row_middle_dashlet: '[name=dashlet_4]',
                second_row_right_dashlet: '[name=dashlet_5]',
                third_row_left_dashlet: '[name=dashlet_6]',
                third_row_middle_dashlet: '[name=dashlet_7]',
                third_row_right_dashlet: '[name=dashlet_8]',

                // Default Renewals Console
                dashboard2by2_top_right: '[name=dashlet_2]',
                dashboard2by2_bottom_right: '[name=dashlet_3]',
                dashboard2by2_top_left: '[name=dashlet_0]',
                dashboard2by2_bottom_left: '[name=dashlet_1]',
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

    /**
     * Switch tabs in Service Console and Renewals Console
     *
     * @param {string} tabName
     */
    public async switchTab(tabName: string) {
        let selector = this.$('tab', {tabName});
        await this.driver.click(selector);
    }

    /**
     * Close side drawer in  Service Console and Renewals Console
     */
    public async closeSideDrawer() {
        let selector = this.$('closeSideDrawer');
        await this.driver.click(selector);
    }
}
