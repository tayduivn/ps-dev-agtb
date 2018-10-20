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
import {seedbed} from '@sugarcrm/seedbed';

/**
 * Represents Record view.
 *
 * @class RecordView
 * @extends BaseView
 */
export default class RecordView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.record',
            panel_body: 'div[data-panelname=\'panel_body\']',
            panel_shipping_body: 'div[data-panelname=\'panel_shipping_body\']',
            panel_setting_body: 'div[data-panelname=\'panel_setting_body\']',
            panel_hidden: 'div[data-panelname=\'panel_hidden\']',
            title: '.title',
            arrow: '.icon-chevron-right',

            listingItem: {
                $: 'a[href$=\'{{module}}\']',
                count: '.records-count',
                label: '.label-module-sm.label-{{label}}'
            },
            listingItemCreateLink: "a[href$='{{module}}/create']",
        });

    }

    public async getSelector(panelName) {
        let panelSelector = null;

        switch (panelName) {
            case 'Business_Card':
                panelSelector = this.$('panel_body');
                break;
            case 'Billing_and_Shipping':
                panelSelector = this.$('panel_shipping_body');
                break;
            case 'Quote_Settings':
                panelSelector = this.$('panel_setting_body');
                break;
            case 'Show_More':
                panelSelector = this.$('panel_hidden');
                break;
        }
        return panelSelector;
    }

    /**
     * Get panel Selector on quote record view
     *
     * @param string panelName
     * @returns {Promise<void>}
     */
    public async togglePanel(panelName) {
        let panelSelector = await this.getSelector(panelName);
        await this.driver.scroll(panelSelector);
        await this.driver.click(panelSelector);
    }

    /**
     * Expand selected panel on quote record view if not already expanded
     *
     * @param string panelName
     * @returns {Promise<void>}
     */
    public async expandQuotePanel(panelName) {
        let panelSelector = await this.getSelector(panelName);
        let expandedPanelSelector = panelSelector + '.panel-active';
        let isExpanded = await this.driver.isElementExist(expandedPanelSelector);

        if (!isExpanded) {
            await this.driver.scroll(panelSelector);
            await this.driver.click(panelSelector);
        }
    }

    /**
     * Collapse selected panel on quote record view if not already collapsed
     *
     * @param string panelName
     * @returns {Promise<void>}
     */
    public async collapseQuotePanel(panelName) {
        let panelSelector = await this.getSelector(panelName);
        let expandedPanelSelector = panelSelector + '.panel-active';
        let isExpanded = await this.driver.isElementExist(expandedPanelSelector);

        if (isExpanded) {
            await this.driver.scroll(panelSelector);
            await this.driver.click(panelSelector);
        }
    }
}
