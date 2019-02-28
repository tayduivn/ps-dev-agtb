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
import {BaseField} from './base-field';

/**
 * @class FloatField
 * @extends BaseField
 */
class FloatField extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: 'input'
            }
        });
    }

    public async setValue(val: any): Promise<void> {
        await this.driver.setValue(this.$('field.selector'), val);
    }
}

export const Edit = FloatField;

export class Detail extends FloatField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div:not(.pull-right)',
                original: '.original',
                converted: '.converted',
                position: '.pull-right'
            }
        });
    }

    public async getText(selector: string): Promise<string> {

        /*  Separate original from converted amount in case the amount is represented
         *  by two numbers: original and converted. (Example: '€90.00 $100.00')
         */
        const elementExists = await this.driver.isExisting(this.$('field.converted'));

        if (elementExists) {
            // Get original amount
            let valueOriginal: string = await this.driver.getText(this.$('field.original'));
            // Get converted amount
            let valueConverted: string = await this.driver.getText(this.$('field.converted'));
            // Return both values with space in between
            return valueOriginal.trim() + ' ' + valueConverted.trim();

        } else {
            let value: string | string [] = await this.driver.getText(this.$('field.selector'));
            return value.toString().trim();
        }
    }
}

export class List extends FloatField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div',
            }
        });

    }

}

export const Preview = Detail;

