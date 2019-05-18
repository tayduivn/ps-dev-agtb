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

/**
 * @class TimeselectField
 * @extends BaseField
 */

export default class TimeselectField extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                input: 'input',
                matchInput: '.ui-timepicker-list .ui-timepicker-selected',
            }
        });
    }

    public async setValue(val: any): Promise<void> {
        await this.driver.click(this.$('field.input'));
        await this.driver.waitForApp();
        await this.driver.setValue(this.$('field.input'), val);
        await this.driver.waitForApp();
        await this.driver.click(this.$('field.matchInput'));
        await this.driver.waitForApp();
    }
}

export class Detail extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: 'div.ellipsis_inline',
            }
        });
    }

    public async getText(selector: string): Promise<string> {
        let value: string | string[] = await this.driver.getText(this.$('field.selector'));
        return value.toString().trim();
    }
}
