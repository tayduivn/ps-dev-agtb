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
 * @class UrlField
 * @extends BaseField
 */
export default class UrlField extends BaseField {

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
        await seedbed.client.setValue(this.$('field.selector'), val);
    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await seedbed.client.getValue(this.$('field.selector'));

        return value.toString().trim();

    }
}


export const Edit = UrlField;

export class Detail extends UrlField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'a'
            }
        });

    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await seedbed.client.getText(this.$('field.selector'));

        return value.toString().trim();

    }
};

export class List extends Detail {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'a'
            }
        });

    }

};

export const Preview = Detail;

