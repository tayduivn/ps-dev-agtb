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
({
    extendsFrom: 'RecordView',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.plugins = _.union(this.plugins || [], ['HistoricalSummary']);
        this._super('initialize', [options]);
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        // render the main Quote Record
        this._super('_render');

        // create the record-totals View
        var $totalsEl = app.view.createView({
            context: this.context,
            type: 'record-totals',
            module: this.module,
            layout: this.layout,
            model: this.model
        });

        // render record-totals View
        $totalsEl.render();

        // Add the record-totals View as the first item inside the .record div
        this.$('.record').prepend($totalsEl.$el);
    }
});
