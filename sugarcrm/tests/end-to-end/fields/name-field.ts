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
 * @class NameField
 * @extends BaseField
 */

export default BaseField;

export class Detail extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div'
            }
        });
    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await seedbed.client.getText(selector);

        return value.toString().trim();

    }
};

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

        let value: string | string[] = await seedbed.client.getText(selector);

        return value.toString().trim();

    }

};


export class Edit extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: 'input'
            }
        });

    }

    /**
     * Set a value for the field
     *
     * @param val
     * @returns {Promise<Client<void>>}
     */
    public async setValue(val: any): Promise<void> {
        await seedbed.client.setValue(this.$('field.selector'), val);
    }

};

export const Preview = Detail;
