/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
({
    extendsFrom: 'CreateView',
    showHelpText: false,
    showErrorDecoration: false,
    events: {
        'click a[name=show_help_text]:not(.disabled)': 'toggleHelpText',
        'click a[name=display_error_state]:not(.disabled)': 'toggleErrorDecoration'
    },

    _render: function() {
        this._super('_render');
        var error_string = 'You did a bad, bad thing.';
        if (this.showErrorDecoration) {
            _.each(this.fields, function(field) {
                if (!_.contains(['button','rowaction','actiondropdown'], field.type)) {
                    field.setMode('edit');
                    field._errors = error_string;
                    if (field.type === 'email') {
                        var errors = {email: ['primary@example.info']};
                        field.handleValidationError([errors]);
                    } else {
                        if (_.contains(['image','picture','avatar'], field.type)) {
                            field.handleValidationError(error_string);
                        } else {
                            field.decorateError(error_string);
                        }
                    }
                }
            }, this);
        }
    },

    _renderField: function(field) {
        app.view.View.prototype._renderField.call(this, field);
        var error_string = 'You did a bad, bad thing.';
        if (!this.showHelpText) {
            field.def.help = null;
            field.options.def.help = null;
        }
    },

    toggleHelpText: function() {
        this.showHelpText = !this.showHelpText;
        this.render();
    },

    toggleErrorDecoration: function() {
        this.showErrorDecoration = !this.showErrorDecoration;
        this.render();
    }
})
