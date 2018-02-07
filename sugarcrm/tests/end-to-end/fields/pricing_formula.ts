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

export {List, Preview} from './enum-field';
import {Edit} from './enum-field';

/**
 * @class EnumField
 * @extends BaseField
 */

export class PricingFormulaEdit extends Edit {

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

        let values = val.split(':');

        await this.driver.click(this.$('field.selector'));
        await this.driver.waitForApp();
        await this.driver.click(`${this.itemSelector}${values[0].trim()}`);

        if (values.length > 1) {
            await this.driver.setValue(this.$('field') + ' #pricing_factor', values[1].trim());
        }
    }

}
