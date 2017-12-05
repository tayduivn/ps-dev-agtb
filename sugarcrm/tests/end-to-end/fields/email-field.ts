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

/**
 * @class EmailField
 * @extends BaseField
 */

export default class EmailField extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[data-name={{name}}]',
            field: {
                 input: '.newEmail',
            }
        });

    }

    public async setValue(val: any): Promise<void> {
        await this.driver.click(this.$('field'));
        await this.driver.setValue(this.$('field.input'), val);
        await this.driver.waitForApp();

    }

}

export class Detail extends EmailField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'span.ellipsis_inline'
            }
        });

    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await this.driver.getText(this.$('field.selector'));

        return value.toString().trim();

    }
}

