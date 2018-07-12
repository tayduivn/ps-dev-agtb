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
/**
 * @class View.Fields.Base.WorklogField
 * @alias SUGAR.App.view.fields.BaseWorklogField
 * @extends View.Fields.Base.BaseField
 */
({
    fieldTag: 'textarea',
    name: 'worklog',

    /**
     * Called when initializing the field
     * @param options
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        if (!this.model.has(this.name)) {
            this.model.setDefault(this.name, []);
        }
    },

    /**
     * Called when rendering the field
     * @private
     */
    _render: function() {
        this.showWorklog();

        this._super('_render'); // everything showing in the UI should be done before this line.
    },

    /**
     * Called when formatting the value for display
     * @param value
     */
    format: function(value) {
        if (_.isString(value)) {
            return value;
        }

        if (this.tplName == 'edit') {
            return '';
        }

        return _.map(value, function(entry) {
            return entry;
        });
    },

    /**
     * Builds model for handlebar to show pass worklog messages in record view.
     * This should only be called when there is need to render past messages, only
     * when this.getFormattedValue() returns the data format for message
     * */
    showWorklog: function() {
        var self = this;
        var value = this.getFormattedValue();

        if (!_.isString(value)) {
            this.msgs = [];
            // recursively get out all data
            _.each(value, function(msg) {
                self.msgs.push(msg);
            });
        }
    },

    /**
     * Called when unformatting the value for storage
     * @param value
     */
    unformat: function(value) {
        return value;
    }
})
