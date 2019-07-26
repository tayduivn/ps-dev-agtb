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
 * @class DurationField
 * @extends BaseField
 */
export default class DurationField extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: 'input[data-type="date"]',
            },
            startField: {
                $: '.fieldset-field[data-name=date_start]',
                date: 'input[data-type="date"]',
                time: 'input[data-type="time"]',
            },
            endField: {
                $: '.fieldset-field[data-name=date_end]',
                date: 'input[data-type="date"]',
                time: 'input[data-type="time"]',
            }
        });
    }

    public async setValue(val: any): Promise<void> {

        let dates;
        let date;
        let time;

        if (val) {
            dates = val.trim().split('~');
        }

        for (let i = 0; i < dates.length; i++) {
            if (dates[i]) {
                let datetime = dates[i].trim().split('-');
                date = datetime[0].trim();
                time = datetime[1].trim();
            }

            // Set Date
            if (date) {
                let selector = (i === 0) ? 'startField.date' : 'endField.date';
                await this.driver.setValue(this.$(selector), date);
            }

            // Set Time
            if (time) {
                let selector = (i === 0) ? 'startField.time' : 'endField.time';
                await this.driver.click(this.$(selector));
                await this.driver.scroll('li=' + time);
                await this.driver.click('li=' + time);
            }
            await this.driver.click('body');
        }
    }
}
