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
import {seedbed} from '@sugarcrm/seedbed';


export class Edit extends BaseField {

    private itemSelector: string;
    private inputSelector: string;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector_type: '.flex-relate-module .select2-container.select2',
                selector_id: '.flex-relate-record .select2-container.select2',
            }
        });

        this.itemSelector = '.select2-result-label=';
        this.inputSelector = '.select2-input.select2-focused';

    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await this.driver.getText(selector);

        return value.toString().trim();

    }

    public async setValue(val: any): Promise<void> {
        let type = val.split(',')[0];
        let id = val.split(',')[1];
        await this.driver.click(this.$('field.selector_type'));
        await this.driver.setValue(this.inputSelector, type);

        await this.driver.waitForApp();
        await this.driver.click(`${this.itemSelector}${type}`);

        await this.driver.click(this.$('field.selector_id'));
        await this.driver.setValue(this.inputSelector, id);

        // need to handle setTimeout 400ms in search box
        await this.driver.pause(500);

        await this.driver.waitForApp();
        await this.driver.click(`${this.itemSelector}${id}`);
    }

}
export class List extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'a'
            }
        });
    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await this.driver.getText(this.$('field'));
        return value.toString().trim();
    }
}

export const Preview = List;
