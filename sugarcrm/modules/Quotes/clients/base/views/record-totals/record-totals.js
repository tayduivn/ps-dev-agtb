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
    /**
     * Object that contains an array of fields for the template
     */
    totalsRow: undefined,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.totalsRow = {
            fields: []
        };

        // build the totalsRow object
        _.each(_.first(this.meta.panels).fields, function(field) {
            this.totalsRow.fields.push({
                label: app.lang.get(field.label, 'Quotes'),
                value: this.model.get(field.name) || '0.00',
                name: field.name
            });
        }, this);
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        // render the field into the its placeholder
        this._super('_render');

        // render record-totals.hbs into this.$el using the totalsRow for data
        this.$el.html(this.template(this.totalsRow));

        // add the recordTotalsWrapper class to the div sidecar adds to views for styling
        this.$el.addClass('recordTotalsWrapper');

        return this;
    }
});
