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
 * @class TeamsetField
 * @extends BaseField
 */

export class Detail extends BaseField {
    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: '.control-group.teamset'
            }
        });
    }

    public async getText(selector: string): Promise<string> {
        let value: string | string[] = await this.driver.getText(selector);
        return value.toString().replace(/\n/g, ',').trim();
    }
}

export class Edit extends BaseField {
    private itemSelector: string;
    private inputSelector: string;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: '.control-group:last-child .select2-container.select2',
                plus: '.btn.first .fa.fa-plus',
                minus: '.control-group:last-child .fa-minus',
                star: '.control-group:last-child .fa-star'
            }
        });

        this.itemSelector = '.select2-result-label=';
        this.inputSelector = '.select2-input.select2-focused';
    }

    public async setValue(val: any): Promise<void> {

        let action = val.split(':').shift().trim().toLowerCase();
        let value = val.split(':').pop().trim();

        // Add another team field by click on the plus button
       switch (action)  {
           case 'add':
                await this.driver.click(this.$('field.plus'));
           case 'edit':
                await this.driver.click(this.$('field.selector'));
                await this.driver.waitForApp();
                await this.driver.setValue(this.inputSelector, value);
               await this.driver.waitForApp();
                await this.driver.click(`${this.itemSelector}${value}`);
               await this.driver.waitForApp();
                break;
           case 'delete':
               await this.driver.click(this.$('field.minus'));
               await this.driver.waitForApp();
               break;
           case 'primary':
               await this.driver.click(this.$('field.star'));
               break;
           default:
               throw new Error(`Not recognized action: ${action} !`)
       }
    }
}

export const Preview = Detail;
