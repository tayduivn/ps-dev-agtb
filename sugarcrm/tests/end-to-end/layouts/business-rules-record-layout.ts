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

/**
 * Represents a Detail/Record page layout.
 *
 * @class BusinessRulesDesignLayout
 * @extends RecordLayout
 */
export default class BusinessRulesDesignLayout extends RecordLayout {

    constructor(options) {
        super(options);
        this.selectors = this.mergeSelectors({
            fields : '.condition-select',
            operators : '.decision-table-operator',
            values : '.expression-container-cell',
        });
        this.type = 'record';
    }

    public async getFields() {
        let selector = this.$('fields');
        return await this.driver.getText(selector);
    }

    public async getOperators() {
        let selector = this.$('operators');
        return await this.driver.getText(selector);
    }

    public async getValues() {
        let selector = this.$('values');
        return await this.driver.getText(selector);
    }
}
