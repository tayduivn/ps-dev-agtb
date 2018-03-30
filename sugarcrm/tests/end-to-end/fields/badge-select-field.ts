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
 * @class BadgeSelectField
 * @extends BaseField
 */
export default class BadgeSelectField extends BaseField {

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

export class List extends BadgeSelectField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'span'
            }
        });
    }
}
