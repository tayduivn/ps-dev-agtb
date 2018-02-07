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

import BaseField from './text-field';
import {seedbed} from '@sugarcrm/seedbed';

/**
 * @class DiscountField
 * @extends BaseField
 */

export class Edit extends BaseField {

    private itemSelector: String;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: '',
                button: '.btn.dropdown-toggle'
            }
        });

        this.itemSelector = '';

    }

    public async setValue(val: any): Promise<void> {

        await this.driver.click(this.$('field.button'));
        await this.driver.waitForApp();
        await this.driver.click(`${this.itemSelector}${val}`);
    }

}


