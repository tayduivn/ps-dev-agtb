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
 * @class TagField
 * @extends BaseField
 */

export default class extends BaseField {

    private matchInput: String;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                $: '.select2-search-field',
                input: 'input',
            }
        });

        this.matchInput = '.select2-match';

    }

    public async setValue(val: any): Promise<void> {
        await this.driver.click(this.$('field'));
        await this.driver.setValue(this.$('field.input'), val);
        // await this.driver.waitForApp();
        // TODO: the better approach is to use waitForApp, as above, but a known issue tracked as SBD-358 requires a sleep
        // TODO remove this pause once SBD-358 is fixed, and uncomment the line above.
        await this.driver.pause(4000);
        await this.driver.click(`${this.matchInput}`);
    }

}

export class TagEdit extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: 'span.tag-wrapper',
            }
        });

    }

    public async getText(): Promise<string> {

        let value: string | string[] = await this.driver.getText(this.$('field.selector'));

        return value.toString().trim();

    }

}