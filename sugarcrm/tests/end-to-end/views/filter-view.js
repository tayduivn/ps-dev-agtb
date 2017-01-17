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
