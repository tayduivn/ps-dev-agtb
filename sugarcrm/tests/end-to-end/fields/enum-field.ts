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
};

export class List extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div'
            }
        });

    }

};


export class Edit extends BaseField {

    private itemSelector: String;

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

        await seedbed.client.click(this.$('field.selector'));
        await seedbed.client.waitForApp();
        await seedbed.client.click(`${this.itemSelector}${val}`);
    }

};

export const Preview = Detail;
