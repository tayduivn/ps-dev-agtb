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

import BaseView from './base-view';

/**
 * Represents Accordion in Quote Config.
 *
 * @class IntelligencePane
 * @extends BaseView
 */
export default class IntelligencePane extends BaseView {
    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.side',
            searchField: '.howto.quotes-config .searchbox',
            field: '.howto.quotes-config .field-list span[field-name={{field_name}}] label',
        });
    }

    /**
     *  Check specified check box found by the field name in Intelligence Pane of quote config
     *
     * @param field_name
     * @returns {Promise<void>}
     */
    public async checkFieldByName(field_name) {
        let locator = this.$('field', {field_name});
        if (await this.driver.isElementExist(locator)) {
            await this.driver.waitForVisible(locator);
            await this.driver.click(locator);
        }
    }

    /**
     * Enter specified string into Search box of Intelligence Pane of quote config
     *
     * @param value
     * @returns {Promise<void>}
     */
    public async setSearchField(value) {
        let locator = this.$('searchField');
        if (await this.driver.isElementExist(locator)) {
            await this.driver.waitForVisible(locator);
            await this.driver.setValue(locator, value);
        }
    }
}
