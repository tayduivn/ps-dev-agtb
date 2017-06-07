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
            panel_body: 'div[data-panelname=\'panel_body\'] .pull-right',
            panel_shipping_body: 'div[data-panelname=\'panel_shipping_body\'] .pull-right',
            panel_setting_body: 'div[data-panelname=\'panel_setting_body\'] .pull-right',
            panel_hidden: 'div[data-panelname=\'panel_hidden\'] .pull-right',
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

    public async togglePanel(panelName) {

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

        await seedbed.client.click(panelSelector);

    }
}
