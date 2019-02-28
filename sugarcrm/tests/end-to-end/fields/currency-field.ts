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
 * @class CurrencyField
 * @extends BaseField
 */
class CurrencyField extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: 'input:not([class *= select2])'
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

export class Edit extends CurrencyField {

    constructor(options) {
        super(options);
    }

    public async getText(selector: string): Promise<string> {
        let value: string | string[] = await this.driver.getValue(this.$('field.selector'));
        return value.toString().trim();
    }
}

export class DetailQLIPercent extends CurrencyField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: '.quote-totals-row-value'
            }
        });

    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await this.driver.getText(this.$('field.selector'));

        // trim all new line characters
        let newValue = value.toString().trim().replace(/\r?\n?/g, '');

        // find position of "%" symbol
        let index = newValue.indexOf('%');
        if (index === -1) {
            return newValue;
        } else {
            // insert space between percentage and dollar amount
            return newValue.substr(0, index + 1) + ' ' + newValue.substr(index + 1);
        }
    }
}

export class DetailQLI extends CurrencyField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div.quote-totals-row-value'
            }
        });

    }

    public async getText(selector: string): Promise<string> {
        let value: string | string[] = await this.driver.getText(this.$('field.selector'));
        return value.toString().trim();
    }
}

export class QLITableFooterShipping extends CurrencyField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: '.quote-footer-currency-value'
            }
        });

    }

    public async getText(selector: string): Promise<string> {
        let value: string | string[] = await this.driver.getText(this.$('field.selector'));
        return value.toString().trim();
    }
}

export class QLITableFooterOther extends CurrencyField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: '.quote-footer-value'
            }
        });

    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await this.driver.getText(this.$('field.selector'));
        return value.toString().trim();
    }
}

export class Detail extends CurrencyField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: '.currency-field',
                input: 'input:not([class *= select2])',
                original: '.original',
                converted: '.converted',
            }
        });
    }

    public async setValue(val: any): Promise<void> {

        await this.driver.scroll(this.$('field.selector'));
        await this.driver.click(this.$('field.selector'));
        await this.driver.setValue(this.$('field.input'), val);
        await this.driver.execSync('blurActiveElement');
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
            return valueOriginal.trim() + ' ' + valueConverted.trim();
        } else {
            let value: string | string [] = await this.driver.getText(this.$('field.selector'));
            return value.toString().trim();
        }
    }
}

export class List extends CurrencyField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div'
            }
        });
    }
}

export const Preview = Detail;
