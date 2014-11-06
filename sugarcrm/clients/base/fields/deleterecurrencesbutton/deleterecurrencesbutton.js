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
/**
 * @class View.Fields.Base.DeleterecurrencesbuttonField
 * @alias SUGAR.App.view.fields.BaseDeleterecurrencesbuttonField
 * @extends View.Fields.Base.Rowaction
 */
({
    extendsFrom: 'RowactionField',

    /**
     * @inheritdoc
     * @param options
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.type = 'rowaction';
    },

    /**
     * @inheritdoc
     *
     * Button should be hidden if meeting displayed is not recurring
     */
    _render: function() {
        if (_.isEmpty(this.model.get('repeat_type'))) {
            this.hide();
        } else {
            this._super('_render');
        }
    },

    /**
     * Re-render the field when the status on the record changes.
     */
    bindDataChange: function() {
        if (this.model) {
            this.model.on('change:repeat_type', this.render, this);
        }
    },

    /**
     * Event handler for deleting all recurring calendar events of a series
     * @inheritdoc
     */
    rowActionSelect: function() {
        app.alert.show('delete_recurrence_confirmation', {
            title: app.lang.get('LBL_REMOVE_ALL_RECURRENCES', this.module),
            level: 'confirmation',
            messages: this.getDeleteMessages().confirmation,
            onConfirm: _.bind(this.deleteRecurrences, this)
        });
    },

    /**
     * Calls destroy on the model and makes api call to delete all recurring calendar events in a series.
     * Navigates to the list view on success.
     */
    deleteRecurrences: function() {
        this.model.destroy({
            params: {'all_recurrences': true},
            showAlerts: {
                'process': true,
                'success': {
                    messages: this.getDeleteMessages().success
                }
            },
            success: _.bind(function() {
                var route = '#' + this.module,
                    currentRoute = '#' + Backbone.history.getFragment();
                (currentRoute === route) ? app.router.refresh() : app.router.navigate(route, {trigger: true});
            }, this)
        });
    },

    /**
     * Formats the messages to display in the alerts when deleting recurrences.
     *
     * @return {Object} The list of messages.
     * @return {string} return.confirmation Confirmation message.
     * @return {string} return.success Success message.
     */
    getDeleteMessages: function() {
        var messages = {};
        var model = this.model;
        var name = app.utils.getRecordName(model);
        var context = app.lang.getModuleName(model.module).toLowerCase() + ' ' + name.trim();

        messages.confirmation = app.lang.get('LBL_CONFIRM_REMOVE_ALL_RECURRENCES', this.module);
        messages.success = app.utils.formatString(app.lang.get('NTC_DELETE_SUCCESS'), [context]);
        return messages;
    }
})
