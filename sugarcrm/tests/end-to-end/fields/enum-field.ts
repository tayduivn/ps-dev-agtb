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
import * as _ from 'lodash';

/**
 * @class EnumField
 * @extends BaseField
 */

export class Edit extends BaseField {

    protected itemSelector: String;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: '.select2-container.select2'
            }
        });

        this.itemSelector = '.select2-result-label=';
    }

    public async setValue(val: any): Promise<void> {

        await this.driver.click(this.$('field.selector'));
        await this.driver.waitForApp();
        await this.driver.click(`${this.itemSelector}${val}`);
    }
}

export class Detail extends BaseField {

    protected itemSelector: String;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div.ellipsis_inline'
            }
        });

        this.itemSelector = '.select2-result-label=';
    }


    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await this.driver.getText(selector);
        return value.toString().trim();
    }

    public async setValue(val: any): Promise<void> {

        await this.driver.click(this.$('field.selector'));
        await this.driver.waitForApp();
        await this.driver.click(`${this.itemSelector}${val}`);
    }

}

export class List extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div.ellipsis_inline'
            }
        });
    }
}

export const Preview = List;

export class EditBWC extends BaseField {
    constructor(options: any) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: `select[name={{name}}]`,
            field: {
                selector: ``,
            },
            options: 'option',
            option: "option[value='{{value}}']",
        });
    }

    public async setValue(value): Promise<any> {
        let select = this.$();
        let option = this.$('option', {
            name: this.name,
            value: value.replace(' ', '').toLowerCase() || '',
        });

        await this.driver.click(select);
        await this.driver.waitForAnimation();
        await this.driver.click(option);
    }

    public async getText(): Promise<string> {
        let selector = this.$('', { name: this.name });
        let visible = await this.driver.isVisible(selector);
        if (!visible) {
            throw new Error(`Field is not visible: '${selector}'`);
        }

        let result = await this.driver.execSync('getSelectedOptionsText', [
            selector,
        ]);
        return _.trim(result.value);
    }

    public getOptions(): any {
        let selector = this.$('field.options', { name: this.name });

        return this.driver
            .execSync('getNestedElementsText', [selector])
            .then(result => (result.value ? result.value : []));
    }

    public getEnumOptions(): any {
        return this.getOptions().then(value => {
            return value.join(', ').trim();
        });
    }
}

export default EditBWC;
