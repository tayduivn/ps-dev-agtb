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
 * @class IntField
 * @extends BaseField
 */
export default class IntField extends BaseField {

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

export const Edit = IntField;

export class Detail extends IntField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div'
            }
        });

    }
}

/**
 *  This class handling field empty value case in record/list/preview views
 *  to the field's css path which this class is taking advantage of.
 */
export class DetailEmptyValue extends BaseField {

    constructor(options) {
        super(options);
        this.selectors = this.mergeSelectors({
            $: '.disabled[field-name={{name}}]',
            field: {
                selector: '',
            }
        });
    }
}

export const Preview = Detail;
export const List = Detail;
