/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
({
    extendsFrom: 'CreateView',

    initialize: function(options) {
        this._super("initialize", [options]);
        this._parsePanelFields(this.meta.panels);
    },

    /**
     * Parse the fields in the panel for the different requirement that we have
     *
     * @param {Array} panels
     * @protected
     */
    _parsePanelFields: function(panels) {
        _.each(panels, function(panel) {
            if (!app.metadata.getModule("Forecasts", "config").is_setup) {
                // use _.every so we can break out after we found the commit_stage field
                _.every(panel.fields, function(field, index) {
                    if (field.name == 'commit_stage') {
                        panel.fields[index] = {
                            'name': 'spacer',
                            'span': 6,
                            'readonly': true
                        };
                        return false;
                    }
                    return true;
                }, this);
            }
        }, this);
    }
})
