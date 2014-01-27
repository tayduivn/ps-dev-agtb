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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
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
     * Call archive api.
     */
    archive: function() {
        var archiveUrl = app.api.buildURL('Mail/archive'),
            alertKey = 'mail_archive',
            archiveEmailModel = this.initializeSendEmailModel();

        this.setMainButtonsDisabled(true);
        app.alert.show(alertKey, {level: 'process', title: app.lang.get('LBL_EMAIL_ARCHIVING', this.module)});

        app.api.call('create', archiveUrl, archiveEmailModel, {
            success: function() {
                app.alert.dismiss(alertKey);
                app.alert.show(alertKey, {
                    autoClose: true,
                    level: 'success',
                    messages: app.lang.get('LBL_EMAIL_ARCHIVED', this.module)
                });
                app.drawer.close();
            },
            error: function(error) {
                var msg = {autoClose: false, level: 'error'};
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
     * @returns {*}
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
