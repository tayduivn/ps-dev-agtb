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
 * Represents Accordion in Quote Configuration drawer
 *
 * @class Accordion
 * @extends BaseView
 */
export default class Accordion extends BaseView {
    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.accordion',
            summary_bar: '.config-summary-group .accordion-toggle',
            worksheet_columns: '.config-columns-group .accordion-toggle span',
            grand_totals_footer: '.config-footer-group.accordion-group div',
            restoreDefaults: '.accordion-inner .worksheet-columns-directions .restore-defaults-btn'
        });
    }

    /**
     * Click Restore Default in accordion section of the Quote Configuration drawer
     *
     * @returns {Promise<void>}
     */
    public async restoreDefaults() {
        let selector = this.$('restoreDefaults');
        if (await this.driver.isElementExist(selector)) {
            await this.driver.scroll(selector);
            await this.driver.click(selector);
        }
    }

    /**
     * Expand accordion panel in Quote configuration drawer
     *
     * @param accordionName
     * @returns {Promise<void>}
     */
    public async toggleAccordion(accordionName) {
        let panelSelector = this.$(accordionName);
        if (await this.driver.isElementExist(panelSelector)) {
            await this.driver.scroll(panelSelector);
            await this.driver.click(panelSelector);
        }
    }
}
