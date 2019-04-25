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

            buttons: {
                open_address_book_btn: '.address-book[data-name="to_collection"]',
                open_address_book_btn_cc: '.address-book[data-name="cc_collection"]',
                open_address_book_btn_bcc: '.address-book[data-name="bcc_collection"]',
                cc_button: '[data-toggle-field="cc_collection"]',
                cc_button_active: '.active[data-toggle-field="cc_collection"]',
                bcc_button: '[data-toggle-field="bcc_collection"]',
                bcc_button_active: '.active[data-toggle-field="bcc_collection"]',
                activate_recipents_field: '.email-recipients.fieldset',
            },
            // Knowledge Base module
            templates: '.load-template a',
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

    /**
     * Check if element is present on the record view
     *
     * @param elementName
     * @returns {Promise<>}
     */
    public async elementExists(elementName) {
        let selector = this.$(elementName);
        let value = await this.driver.isElementExist(selector);
        return value;
    }

    /**
     * Click Templates button on Knowledge Base create drawer
     *
     * @returns {Promise<void>}
     */
    public async clickTemplatesButton() {
        let selector = this.$('templates');
        await this.driver.scroll(selector);
        await this.driver.click(selector);
    }
}
