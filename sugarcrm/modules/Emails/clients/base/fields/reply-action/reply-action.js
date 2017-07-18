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
 * Reply or reply all action.
 *
 * This allows an user to "reply" or "reply all" to an existing email.
 *
 * @class View.Fields.Base.ReplyActionField
 * @alias SUGAR.App.view.fields.BaseReplyActionField
 * @extends View.Fields.Base.EmailactionField
 */
({
    extendsFrom: 'EmailactionField',

    /**
     * Template for reply header.
     *
     * @private
     */
    _tplHeaderHtml: null,

    /**
     * @inheritdoc
     *
     * Sets up the reply content to be used when the user clicks on the Reply or
     * Reply All button. Also listens for changes to the model to update the
     * reply content. The reply content is built ahead of the button click
     * to support the option of doing a mailto link which needs to be built and
     * set in the DOM at render time.
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        //Use field template from emailaction
        this.type = 'emailaction';
    },

    /**
     * Sets up the email options for the EmailClientLaunch plugin to use -
     * passing to the email compose drawer or building up the mailto link.
     *
     * @protected
     */
    _updateEmailOptions: function() {
        var replyRecipients = this._getReplyRecipients(this.def.reply_all);
        var subject = this._getReplySubject(this.model.get('name'));
        var replyHeader = this._getReplyHeader(this._getReplyHeaderParams());
        var tplHeaderHtml = this._getHeaderHtmlTemplate();
        var replyHeaderHtml = tplHeaderHtml(this._getReplyHeaderParams());
        var replyBody = this._getReplyBody();
        var replyBodyHtml = this._getReplyBodyHtml();
        // The "replycontent" ID is added to the div wrapper around the reply
        // content for later identifying the portion of the email body which is
        // the reply content (e.g., when inserting templates into an email, but
        // maintaining the reply content).
        var descriptionHtml = '<div></div><div id="replycontent">' +
            replyHeaderHtml + replyBodyHtml + '</div>';

        var options = {
            to: replyRecipients.to,
            cc: replyRecipients.cc,
            name: subject,
            description_html: descriptionHtml,
            parent_type: this.model.get('parent_type'),
            parent_id: this.model.get('parent_id'),
            parent_name: this.model.get('parent_name'),
            team_name: this.model.get('team_name'),
            reply_to_id: this.model.get('id'),
            signature_location: 'above'
        };

        if (!this.useSugarEmailClient()) {
            options.description = '\n' + replyHeader + '\n' + replyBody;
        }

        this.addEmailOptions(options);
    },

    /**
     * Return the HTML reply header template if it exists, or retrieves it.
     *
     * @return {Function}
     * @private
     */
    _getHeaderHtmlTemplate: function() {
        this._tplHeaderHtml = this._tplHeaderHtml ||
            app.template.getField(this.type, 'reply-header-html', this.module);
        return this._tplHeaderHtml;
    },

    /**
     * Build the reply recipients based on the original email's from, to, and cc
     *
     * @param {boolean} all Whether this is reply to all (true) or just a standard
     *   reply (false).
     * @return {Object} To and Cc values for the reply email.
     * @return {Array} return.to The to values for the reply email.
     * @return {Array} return.cc The cc values for the reply email.
     * @protected
     */
    _getReplyRecipients: function(all) {
        var replyTo = [];
        var replyCc = [];
        var originalSender = this.model.get('from_collection');
        var originalTo = this.model.get('to_collection');
        var originalCc = this.model.get('cc_collection');

        var mapRecipients = function(recipients) {
            return _.map(recipients, function(recipient) {
                var data = {
                    email: app.data.createBean('EmailAddresses', {
                        id: recipient.get('email_address_id'),
                        email_address: recipient.get('email_address')
                    })
                };

                // The type and id fields are not unset after a parent record
                // is deleted. So we test for name because the parent record is
                // truly only there if type and id are non-empty and the parent
                // record can be resolved and has not been deleted.
                if (recipient.get('parent') &&
                    recipient.get('parent').type &&
                    recipient.get('parent').id &&
                    recipient.get('parent').name
                ) {
                    // We omit type because it is actually the module name and
                    // should be treated as an attribute.
                    data.bean = app.data.createBean(
                        recipient.get('parent').type,
                        _.omit(recipient.get('parent'), 'type')
                    );
                }

                return data;
            });
        };

        if (originalSender && originalSender.models) {
            replyTo = _.union(replyTo, mapRecipients(originalSender.models));
        }

        if (all && originalTo && originalTo.models) {
            replyTo = _.union(replyTo, mapRecipients(originalTo.models));
        }

        if (all && originalCc && originalCc.models) {
            replyCc = _.union(replyCc, mapRecipients(originalCc.models));
        }

        return {
            to: replyTo,
            cc: replyCc
        };
    },

    /**
     * Given the original subject, generate a reply subject.
     *
     * @param {string} subject
     * @protected
     */
    _getReplySubject: function(subject) {
        var pattern = /^((?:re|fwd): *)*/i;
        subject = subject || '';
        return 'Re: ' + (subject.replace(pattern, '') || '');
    },

    /**
     * Get the params required to run the reply header template.
     *
     * @return {Object}
     * @protected
     */
    _getReplyHeaderParams: function() {
        return {
            from: this._formatEmailList(this.model.get('from_collection')),
            date: this.model.get('date_sent'),
            to: this._formatEmailList(this.model.get('to_collection')),
            cc: this._formatEmailList(this.model.get('cc_collection')),
            name: this.model.get('name')
        };
    },

    /**
     * Build the reply header for text only emails.
     *
     * @param {Object} params
     * @param {string} params.from
     * @param {string} [params.date] Date original email was sent
     * @param {string} params.to
     * @param {string} [params.cc]
     * @param {string} params.name The subject of the original email.
     * @return {string}
     * @private
     */
    _getReplyHeader: function(params) {
        var date = app.date(params.date).formatUser();
        var header = '-----\n' + 'From: ' + (params.from || '') + '\n';

        if (params.date) {
            header += 'Date: ' + date + '\n';
        }

        header += 'To: ' + (params.to || '') + '\n';

        if (params.cc) {
            header += 'Cc: ' + params.cc + '\n';
        }

        header += 'Subject: ' + (params.name || '') + '\n';

        return header;
    },

    /**
     * Given a list of people, format a text only list for use in a reply header
     *
     * @param {Data.BeanCollection} collection A list of models
     * @protected
     */
    _formatEmailList: function(collection) {
        var result = '';
        var models = collection instanceof app.BeanCollection ? collection.models : [];

        _.each(models, function(model) {
            var name = model.get('parent_name');
            var email = model.get('email_address');

            if (result) {
                result += ', ';
            }

            if (name) {
                result += name + ' <' + email + '>';
            } else {
                result += email;
            }
        }, this);

        return result;
    },

    /**
     * Retrieve the plain text version of the reply body.
     *
     * @return {string} The reply body
     * @private
     */
    _getReplyBody: function() {
        return this.model.get('description') || '';
    },

    /**
     * Retrieve the HTML version of the reply body.
     *
     * Ensure the result is a defined string and strip any signature wrapper
     * tags to ensure it doesn't get stripped if we insert a signature above
     * the reply content. Also strip any reply content class if this is a
     * reply to a previous reply.
     *
     * @return {string} The reply body
     * @private
     */
    _getReplyBodyHtml: function() {
        var body = (this.model.get('description_html') || '');
        body = body.replace('<div class="signature">', '<div>');
        return body.replace('<div id="replycontent">', '<div>');
    }
})
