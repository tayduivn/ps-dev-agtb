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
import {underline} from "chalk";

/**
 * Represents Record view.
 *
 * @class QliRecord
 * @extends BaseView
 */
export default class QliRecord extends BaseView {

    public recordIndex: any;

    constructor(options) {
        super(options);

        this.recordIndex = options.recordIndex;

        this.selectors = this.mergeSelectors({
            $: this.recordIndex ? `.quote-data-group tr:nth-child(${this.recordIndex})` : '[record-id=""]',
            buttons: {
                save: '.btn.inline-save',
                cancel: '.btn.inline-cancel'
            }
        });
    }

    public async pressButton(buttonName) {
        await seedbed.client.click(this.$(`buttons.${buttonName.toLowerCase()}`));
    }

    public async getLineItemTotal(recordName) {

        // $("div[data-original-title='{}']").closest('tr').find('td:last').find('div.currency-field').data('original-title');

        await seedbed.client.getText();
    }

}
