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
 * @class CommentLogField
 * @extends BaseField
 */
export class Edit extends BaseField {
    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: 'textarea'
            }
        });
    }

    public async setValue(val: any): Promise<void> {
        await this.driver.setValue(this.$('field.selector'), val);
    }

    public async getText(selector: string): Promise<string> {
        let value: string | string[] = await this.driver.getValue(this.$('field.selector'));
        return value.toString().trim();
    }
}

export class Detail extends BaseField {
    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: '.msg-content',
                editor: 'textarea'
            }
        });
    }

    public async setValue(val: any): Promise<void> {
        await this.driver.setValue(this.$('field.editor'), val);
    }

    /**
     * @param {string} selector The selector.
     * @return {string} The content and content only of the commentlog.
     *   If multiple commentlog messages are present, there will be a
     *   "," between each commentlog message, in the order of top to down
     *   in the UI.
     */
    public async getText(selector: string): Promise<string> {
        let value: string | string[] = await this.driver.getText(this.$('field.selector'));
        return value.toString();
    }
}
