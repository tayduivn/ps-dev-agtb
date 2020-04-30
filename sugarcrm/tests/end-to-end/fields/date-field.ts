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
import {seedbed} from "@sugarcrm/seedbed";
import {config} from "../config";
import * as _ from "underscore";

/**
 * @class DateField
 * @extends BaseField
 */
export default class DateField extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}][field-type={{type}}]',
            field: {
                selector: 'input',
                cascadeCheckBox: '.' + this.name + '_should_cascade',
            }
        });
    }

    public async setValue(val: any): Promise<void> {
        let isCheckBoxExists = await this.driver.isElementExist(this.$('field.cascadeCheckBox'));
        if(isCheckBoxExists) {
            await this.driver.click(this.$('field.cascadeCheckBox'));
        }

        await this.driver.setValue(this.$('field.selector'), val);
        await this.driver.execSync('blurActiveElement');
    }

    protected prepareValues(
        actualValue: string,
        expectedValue: string
    ): string[] {
        let values = super.prepareValues(actualValue, expectedValue);
        values[1] = seedbed.support.fixDateInput(values[1], this.convertFormat(this.getDateTimePref()));
        return values;
    }

    protected getDateTimePref(): string {
        return this.getAdminPreference('datepref');
    }

    protected getAdminPreference(pref: string): string {
        return config.users.admin.defaultPreferences[pref];
    }

    /**
     * Convert Sugar server to momentjs format
     * @param {string} server
     * @returns {string}
     */
    private convertFormat(server: string): string {

        let convertTable = [
            // date
            ['d', 'DD'],  // day of the month w\ leading zeros
            ['m', 'MM'],  // numeric month w\ leading zeros
            ['Y', 'YYYY'],  // full numeric year
            // time
            ['H', 'HH'], // 24-hour format w\ leading zeros
            ['h', 'hh'], // 12-hour format w\ leading zeros
            ['G', 'H'], // 24-hour format without leading zeros
            ['g', 'h'], // 12-hour format without leading zeros
            ['i', 'mm'], // minutes w\ leading zeros
        ];

        var client = server;
        _.each(convertTable, conversion => {
            let [from, to] = conversion;
            client = client.replace(from, to);
        });
        return client;
    }
}

export const Edit = DateField;

export class Detail extends DateField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div.ellipsis_inline',
                input: 'input',
            }
        });
    }

    public async setValue(val: any): Promise<void> {
        await this.driver.click(this.$('field.selector'));
        await this.driver.setValue(this.$('field.input'), val);
        await this.driver.execSync('blurActiveElement');
    }
}

export class List extends DateField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div.ellipsis_inline'
            }
        });

    }

}

export const Preview = Detail;


/**
 *  This provides support for date field type in Summary Bar of QLI Table section of quote record view
 *
 *  Note: Adding date field type like `Valid Until` to QLI table header is
 *  possible through Admin > Quote Configuration > Summary Bar Header Preview
 */
export class QLISummaryBarDateField extends DateField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'div.quote-totals-row-value'
            }
        });
    }

    public async getText(selector: string): Promise<string> {
        let value: string | string[] = await this.driver.getText(this.$('field.selector'));
        return value.toString().trim();
    }
}
