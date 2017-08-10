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
import {seedbed} from '@sugarcrm/seedbed';
import {BaseField} from './base-field';

/**
 * @class CurrencyField
 * @extends BaseField
 */
export default class CurrencyField extends BaseField {

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
        await seedbed.client.setValue(this.$('field.selector'), val);
    }
}

export class Edit extends CurrencyField {

    constructor(options) {
        super(options);
    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await seedbed.client.getValue(this.$('field.selector'));

        return value.toString().trim();

    }
}


export class Detail extends CurrencyField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div'
            }
        });

    }
};

export class List extends CurrencyField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div'
            }
        });

    }

};

export const Preview = Detail;

