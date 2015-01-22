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
    events: {
        'click [data-spotlight=add]': 'addRow'
    },

    initialize: function(options) {
        this.rowTpl = app.template.get('spotlight-config-list.spotlight-config-list-row');
        this._super('initialize', [options]);
    },

    /**
     * Adds a row.
     * @param {Event} evt The `click` event.
     */
    addRow: function(evt) {
        var $row = this.rowTpl();
        this.$('[data-spotlight=actions]').append($row);
    }
})
