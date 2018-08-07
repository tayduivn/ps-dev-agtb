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
import RecordLayout from './record-layout';
import DrawerLayout from './drawer-layout';

/**
 * Represents a Detail/Record page layout.
 *
 * @class RecordLayout
 * @extends BaseView
 */
export default class PersonalInfoDrawerLayout extends DrawerLayout {

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            row: 'tr[data-fieldname="{{field_name}}"] input[type="checkbox"]',
            rowValue: 'tr[data-fieldname="{{field_name}}"] td[data-column="value"]',
        });

        this.type = 'drawer';
    }

    public async clickRowByFieldName(field_name) {
        await this.driver.click(this.$('row', {field_name}));
    }

    public async getFieldValue(field_name) {
        let selector  = this.$('rowValue', {field_name});
        return await this.driver.getText(selector);
    }
}
