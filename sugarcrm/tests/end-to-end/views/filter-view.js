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
/*
 Represents Filter view PageObject on ListView Layout.
 */

var Cukes = require('@sugarcrm/seedbed'),
    BaseView = Cukes.BaseView;

/**
 * @class SugarCukes.FilterView
 * @extends Cukes.BaseView
 */
class FilterView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = {
            $: '.search-filter',
            searchField: ".search-name"
        };
    }

    /**
     * Set Search field name with "value"
     *
     * @param value
     * @returns {*}
     */
    setSearchField (value) {
        var locator = this.$('searchField');

        return seedbed.client
            .waitForVisible(locator)
            .setValue(locator, value);
    }
}

module.exports = FilterView;
