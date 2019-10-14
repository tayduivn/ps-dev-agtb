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
import {KeyCodes} from '../step_definitions/steps-helper';

/**
 * @class FromField
 * @extends BaseField
 */
export default class FromField extends BaseField {

    private itemSelector: string;
    private inputSelector: string;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: 'div'
            }
        });
        this.itemSelector = '.select2-result-label=';
        this.inputSelector = '.select2-input.select2-focused';
    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await this.driver.getText(this.$('field.selector'));
        return value.toString().trim();
    }

    public async setValue(val: any): Promise<void> {
        await this.driver.click(this.$('field.selector'));
        await this.driver.setValue(this.inputSelector, val);
        // Forcing the pause to wait for the select2 debounce after text entry
        await this.driver.pause(500);
        await this.driver.waitForApp();

        // Confirm new value by click <enter>
        await this.driver.keys(KeyCodes.ENTER);
        await this.driver.waitForApp();
    }
}
