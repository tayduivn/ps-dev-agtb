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
 * @class BoolField
 * @extends BaseField
 */
export default class BoolField extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: 'input',
                cascadeCheckBox: '.' + this.name + '_should_cascade',
            }
        });
    }

    public async setValue(val: string): Promise<void> {

        // In case of service_duration field which is a fieldset of two fields there is a need to activate 'cascade'
        // checkbox before any service duration can be set from opportunity level.
        let isCheckBoxExists = await this.driver.isElementExist(this.$('field.cascadeCheckBox'));
        if(isCheckBoxExists) {
            await this.driver.click(this.$('field.cascadeCheckBox'));
        }

        let curValue = await this.driver.isSelected(this.$('field.selector'));
        // Toggle value only if current check box state is not equal to new state
        if ( val.toLowerCase() !== curValue.toString() ) {
            await this.driver.scroll(this.$('field.selector'));
            await this.driver.click(this.$('field.selector'));
        }
    }

    public async getText(selector: string): Promise<string> {
        let value: boolean =  await this.driver.isSelected(this.$('field.selector'));
        return value.toString();
    }
}

// export const Edit = BoolField;

export class Edit extends BaseField {
    constructor(options: any) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: `input[name={{name}}][type=checkbox]`,
            field: {
                selector: '',
            },
        });
    }

    public async setValue(value): Promise<any> {
        let select = this.$();
        await this.driver.scroll(select);
        await this.driver.click(select);
    }
}
