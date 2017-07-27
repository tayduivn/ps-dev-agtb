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

import BaseField from './enum-field';
import {seedbed} from '@sugarcrm/seedbed';

/**
 * @class TagField
 * @extends BaseField
 */

export default BaseField;

export class Edit extends BaseField {

    private matchInput: String;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[data-name={{name}}]',
            field: {
                $: '.select2-search-field',
                input: 'input',
            }
        });

        this.matchInput = '.select2-match';

    }

    public async setValue(val: any): Promise<void> {
        await seedbed.client.click(this.$('field'));
        await seedbed.client.setValue(this.$('field.input'), val);
        await seedbed.client.waitForApp();
        await seedbed.client.click(`${this.matchInput}`);
    }

};
