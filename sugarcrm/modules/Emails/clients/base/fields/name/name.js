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
 * @class View.Fields.Base.Emails.NameField
 * @alias SUGAR.App.view.fields.BaseEmailsNameField
 * @extends View.Fields.Base.NameField
 */
({
    extendsFrom: 'BaseNameField',

    /**
     * If the model has attachments or not
     */
    hasAttachments: false,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        var attachments;
        this._super('initialize', [options]);

        attachments = this.model.get('attachments');
        if (attachments && attachments.records && attachments.records.length) {
            this.hasAttachments = true;
        }
    },

    /**
     * @inheritdoc
     */
    format: function(value) {
        if (_.isEmpty(value) && this.action !== 'edit') {
            // the subject line is empty, show (no subject) instead of blank
            return app.lang.get('LBL_NO_SUBJECT', this.module);
        }

        return value;
    },

    /**
     * Build email record route depending on whether the email is a draft or
     * not and whether the user has the Sugar Email Client enabled
     *
     * @return {string}
     */
    buildHref: function() {
        var defRoute = this.def.route ? this.def.route : {};
        var module = this.model.module || this.context.get('module');

        if (this.model.get('state') === 'Draft' && this._useSugarEmailClient()) {
            module += '/drafts';
        }

        return '#' + app.router.buildRoute(module, this.model.get('id'), defRoute.action);
    },

    /**
     * Determine if the user is configured to use the Sugar Email Client for
     * editing existing draft emails.
     *
     * @return {boolean}
     * @private
     */
    _useSugarEmailClient: function() {
        var emailClientPreference = app.user.getPreference('email_client_preference');

        return (
            emailClientPreference &&
            emailClientPreference.type === 'sugar' &&
            app.acl.hasAccess('edit', 'Emails')
        );
    }
})
