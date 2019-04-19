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
                adddashlet: '.add-dashlet .fa.fa-plus'
            },
            elements: {
                dashlet: '.dashlets.row-fluid',
                FirstDashlet: '.row-fluid[name="dashlet_00"]',
                SecondDashlet: '.row-fluid[name="dashlet_01"]',
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
