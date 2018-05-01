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


export class Edit extends BaseField {

    private itemSelector: string;
    private inputSelector: string;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.edit[field-name={{name}}]',
            field: {
                selector: '.participants-schedule',
            },
            buttons: {
                addInvitee: '.cell.buttons .fa.fa-plus',
                removeInvitee: '.row.participant[data-id*="{{id}}"] .cell.buttons .fa.fa-minus',
            }
        });

        this.itemSelector = '.select2-result-label*=';
        this.inputSelector = '.select2-input.select2-focused';
    }

    public async getText(selector: string): Promise<string> {

        let value: string | string[] = await this.driver.getText(selector);

        return value.toString().trim();
    }

    public async setValue(val: any): Promise<void> {
        await this.driver.scroll(this.$('field.selector'));

        let arr = val.split(':');

        let action = arr[0].trim();
        val = arr[1].trim();

        let records = val.split(',');
        let record, recordID, name;

        for (let i = 0; i < records.length; i++) {

            record = (records[i].trim()).replace('*', '');

            recordID = seedbed.cachedRecords.get(record);

            if (action === 'add') {
                name = recordID.input.get('last_name');

                await this.driver.click(this.$(`buttons.addInvitee`));

                await this.driver.waitForApp();

                await this.driver.setValue(this.inputSelector, name);
                await this.driver.waitForApp();
                await this.driver.click(`${this.itemSelector}${name}`);

            } else if (action === 'remove') {

                await this.driver.click(this.$('buttons.removeInvitee', {id: recordID.id}));
                await this.driver.waitForApp();
            }
        }
    }
}

export class Detail extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.detail[field-name={{name}}]',
            field: {
                selector: '.participants-schedule .cell.profile .name',
            }
        });
    }

    public async getText(): Promise<string> {

        let value: string | string[] = await this.driver.getText(this.$('field.selector'));

        return value.toString().trim();
    }
}

export class Preview extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: '.participant .cell.name',
            }
        });
    }

    public async getText(): Promise<string> {

        let value: string | string[] = await this.driver.getText(this.$('field.selector'));

        return value.toString().trim();
    }

}
