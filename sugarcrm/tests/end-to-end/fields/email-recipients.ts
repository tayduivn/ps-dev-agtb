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
 * @class EmailRecipientsField
 * @extends BaseField
 */
export default class EmailRecipientsField extends BaseField {
    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div',
                item: {
                    $: '.select2-search-choice div span'
                }
            }
        });
    }

    public async setValue(val: any): Promise<void> {
        await this.driver.click(this.$('field'));
        await this.driver.setValue(this.$('field.input'), val);
        await this.driver.waitForApp();
    }

    public async getText(selector: string): Promise<string> {
        // First check if any recipients exist by looking for pills.
        const recipientsSelector = this.$('field.item');
        const hasRecipients = await this.driver.isExisting(recipientsSelector);

        let value: string | string[] = [];

        // Only get the names of the recipients if there are any.
        if (hasRecipients) {
            value = await this.driver.getText(recipientsSelector);
        }

        // The return value could be a string, so let's protect against it.
        if (!_.isArray(value)) {
            value = [value];
        }

        return value.join(',');
    }
}

export class Edit extends EmailRecipientsField {
    private itemSelector: string;
    private inputSelector: string;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: '.select2-container.select2',
            }
        });

        this.itemSelector = '.select2-result-label=';
        this.inputSelector = '.select2-input.select2-focused';
    }

    public async setValue(val: any): Promise<void> {

        await this.driver.click(this.$('field.selector'));
        await this.driver.setValue(this.inputSelector, val);

        // TODO remove this pause later!!!, waitForApp should handle this case for select2 control
        await this.driver.pause(4000);
        await this.driver.waitForApp();
        await this.driver.click(`${this.itemSelector}${val}`);
    }
}

export const Detail = class EmailRecipientsField extends BaseField {
    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'span'
            }
        });
    }

    public async getText(selector: string): Promise<string> {
        // First check if any recipients exist by looking for pills.
        const recipientsSelector = this.$('field.selector');
        const hasRecipients = await this.driver.isExisting(recipientsSelector);

        let value: string | string[] = [];

        // Only get the names of the recipients if there are any.
        if (hasRecipients) {
            value = await this.driver.getText(recipientsSelector);
        }

        // The return value could be a string, so let's protect against it.
        if (!_.isArray(value)) {
            value = [value];
        }

        return value.join(',');
    }
};

export const List = EmailRecipientsField;
export const Preview = EmailRecipientsField;
