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
/**
 * @class View.Views.Base.Emails.ArchiveEmailView
 * @alias SUGAR.App.view.views.BaseEmailsArchiveEmailView
 * @extends View.Views.Base.Emails.ComposeView
 */
({
    extendsFrom: 'EmailsComposeView',

    /**
     * Add click event handler to archive an email.
     * @param options
     */
    initialize: function(options) {
        this.events = _.extend({}, this.events, {
            'click [name=archive_button]': 'archive'
        });
        this._super('initialize', [options]);

        if (!this.model.has('assigned_user_id')) {
            this.model.set('assigned_user_id', app.user.id);
            this.model.set('assigned_user_name', app.user.get('full_name'));
        }
    },

    /**
     * Set headerpane title.
     * @private
     */
    _render: function () {
        this._super('_render');
        this.setTitle(app.lang.get('LBL_ARCHIVE_EMAIL', this.module));
    },

    /**
     * Archive email if validation passes.
     */
    archive: function(event) {
        this.setMainButtonsDisabled(true);
        this.model.doValidate(this.getFieldsToValidate(), _.bind(function(isValid) {
            if (isValid) {
                this.archiveEmail();
            } else {
                this.setMainButtonsDisabled(false);
            }
        }, this));
    },

    /**
     * Get fields that needs to be validated.
     * @returns {object}
     */
    getFieldsToValidate: function() {
        var fields = {};
        _.each(this.fields, function(field) {
            fields[field.name] = field.def;
        });
        return fields;
    },

    /**
     * Call archive api.
     */
    archiveEmail: function() {
        var archiveUrl = app.api.buildURL('Mail/archive'),
            alertKey = 'mail_archive',
            archiveEmailModel = this.initializeSendEmailModel();

        app.alert.show(alertKey, {level: 'process', title: app.lang.get('LBL_EMAIL_ARCHIVING', this.module)});

        app.api.call('create', archiveUrl, archiveEmailModel, {
            success: _.bind(function() {
                app.alert.dismiss(alertKey);
                app.alert.show(alertKey, {
                    autoClose: true,
                    level: 'success',
                    messages: app.lang.get('LBL_EMAIL_ARCHIVED', this.module)
                });
                app.drawer.close(this.model);
            }, this),
            error: function(error) {
                var msg = {level: 'error'};
                if (error && _.isString(error.message)) {
                    msg.messages = error.message;
                }
                app.alert.dismiss(alertKey);
                app.alert.show(alertKey, msg);
            },
            complete:_.bind(function() {
                if (!this.disposed) {
                    this.setMainButtonsDisabled(false);
                }
            }, this)
        });
    },

    /**
     * Get model that will be used to archive the email.
     * @returns {Backbone.Model}
     */
    initializeSendEmailModel: function() {
        var model = this._super('initializeSendEmailModel');
        model.set({
            'date_sent': this.model.get('date_sent'),
            'from_address': this.model.get('from_address'),
            'status': 'archive'
        });
        return model;
    },

    /**
     * Disable/enable archive button.
     * @param disabled
     */
    setMainButtonsDisabled: function(disabled) {
        this.getField('archive_button').setDisabled(disabled);
    }
})
