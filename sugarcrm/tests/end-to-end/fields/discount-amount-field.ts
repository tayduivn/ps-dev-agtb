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
 * @class DiscountAmountField
 * @extends BaseField
 */
class DiscountAmountField extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: 'input:not([class *= select2])',
                err: '.input.error span',
            }
        });
    }

    public async setValue(val: any): Promise<void> {
        await this.driver.setValue(this.$('field.selector'), val);
    }

    public async getText(selector: string): Promise<string> {
        let value: string | string[] = await this.driver.getValue(this.$('field.selector'));
        return value.toString().trim();
    }
}

export class Edit extends DiscountAmountField {

    constructor(options) {
        super(options);
    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[];

        // Check if there is any field errors when attempting to save record
        let element  =  await this.driver.isElementExist(this.$('field.err'));
        if (element === false) {
            value = await this.driver.getValue(this.$('field.selector'));
            // if field errors are found return error message
        } else {
            value = await this.driver.getAttribute(this.$('field.err'), 'title');
        }
        return value.toString().trim();
    }
}


export class Detail extends DiscountAmountField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: '',
                original: '.original',
                converted: '.converted',
            }
        });
    }


    public async getText(selector: string): Promise<string> {

        /*
         * Separate original from converted amount in case the amount is represented
         * by two numbers: original and converted. (Example: '€90.00 $100.00')
         */

        // Check if '.converted' class exists
        const elementExists = await this.driver.isExisting(this.$('field.converted'));
        if (elementExists) {
            // Get original amount
            let valueOriginal: string = await this.driver.getText(this.$('field.original'));
            // Get converted amount
            let valueConverted: string = await this.driver.getText(this.$('field.converted'));
            // Return both values with space in between
            return `${valueOriginal} ${valueConverted}`;
        } else {
            let value: string | string [] = await this.driver.getText(this.$('field.selector'));
            return value.toString().trim();
        }
    }
}
