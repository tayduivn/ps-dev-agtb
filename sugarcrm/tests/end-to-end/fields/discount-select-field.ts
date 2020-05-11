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

    private itemSelectorQLI: String;
    private itemSelectorOpp: String;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: '',
                button: 'a',
                flag: '.btn.dropdown-toggle',
            }
        });

        // Select item from discount dropdown when add new or edit existing QLI record
        this.itemSelectorQLI = '.rowaction=';
        // Select item from discount dropdown when create new opportunity record
        this.itemSelectorOpp = '.select2-results=';
    }

    public async setValue(val: any): Promise<void> {

        await this.driver.click(this.$('field.button'));
        await this.driver.waitForApp();

        //QLI has some extra classes under discount_select field which allows to differentiate
        //between Opportunity and QLI record
        let isQLI =  await this.driver.isElementExist(this.$('field.flag'));

        if ( isQLI ) {
            await this.driver.click(`${this.itemSelectorQLI}${val}`);
        } else {
            await this.driver.click(`${this.itemSelectorOpp}${val}`);
        }
    }
}
