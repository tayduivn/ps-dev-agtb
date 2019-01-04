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

import BaseView from '../views/base-view';

/**
 * @class ForecastsManagerFooterView
 * @extends BaseView
 */
export default class ForecastsManagerFooterView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $:  'tfoot',
            field: 'td[data-name="{{field_name}}"]'
       });
    }

    /**
     * Get specified field's Total amount on Forecasts Sales Manager worksheet
     *
     * @param {string} field_name
     * @returns {Promise<string>}
     */
    public async getFooterFieldValue(field_name: string) {
        const locator = this.$(`field`, {field_name});
        const value = await this.driver.getText(locator);
        return value.toString().trim();
    }
}
