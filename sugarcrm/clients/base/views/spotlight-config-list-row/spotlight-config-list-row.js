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
    tagName: 'tr',
    className: 'config-list-row',
    events: {
        'click [data-spotlight=remove]': 'removeRow'
    },

    initialize: function(options) {
        options.model = app.data.createBean();
        this._super('initialize', [options]);
    },

    _renderHtml: function() {
        this._super('_renderHtml');
    },

    /**
     * @inheritDoc
     */
    removeRow: function() {
        this.dispose();
        if (this.layout) {
            this.layout.removeComponent(this);
        }
    }
})
