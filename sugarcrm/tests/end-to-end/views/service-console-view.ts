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

import DashboardView from './dashboard-view';

/**
 * Represents Service Console view.
 *
 * @class ServiceConsoleView
 * @extends DashboardView
 */
export default class ServiceConsoleView extends DashboardView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            tab: 'a[data-original-title="{{tabName}}"]',
            closeSideDrawer: '.close-drawer',
        });
    }

    /**
     * Switch tabs in Service Console
     *
     * @param {string} tabName
     */
    public async switchTab(tabName: string) {
        let selector = this.$('tab', {tabName});
        await this.driver.click(selector);
    }

    /**
     * Close side drawer in Cases tab of Service Console
     */
    public async closeSideDrawer() {
        let selector = this.$('closeSideDrawer');
        await this.driver.click(selector);
    }
}
