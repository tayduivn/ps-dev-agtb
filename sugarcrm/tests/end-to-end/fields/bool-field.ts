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
 * @class CopyField
 * @extends BaseField
 */
export default class BoolField extends BaseField {

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
        await this.driver.scroll(this.$('field.selector'));
        await this.driver.click(this.$('field.selector'));
    }

    public async getText(selector: string): Promise<string> {
        let value: boolean =  await this.driver.isSelected(selector);
        return value.toString();
    }
}

export const Edit = BoolField;
