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
 * @class View.Fields.Base.Emails.ReplyActionField
 * @alias SUGAR.App.view.fields.EmailsBaseReplyActionField
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
     * The reply content is built ahead of the button click to support the
     * option of doing a mailto link which needs to be built and set in the DOM
     * at render time.
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        //Use field template from emailaction
        this.type = 'emailaction';

        // If there is a default signature in email compose, it should be
        // placed above the reply content in the email body.
        this.addEmailOptions({signature_location: 'above'});
    },

    /**
     * @inheritdoc
     *
     * Updates the reply_to_id email option anytime the model's id attribute
     * changes.
     */
    bindDataChange: function() {
        var context = this.context.parent || this.context;
        var model = context.get('model');

        this._super('bindDataChange');

        if (model) {
            // Set the reply_to_id email option if the ID already exists.
            this.addEmailOptions({reply_to_id: model.get('id')});

            // Update the reply_to_id email option anytime the ID changes. This
            // might occur if the ID was discovered later. It is an edge-case.
            this.listenTo(model, 'change:id', function() {
                this.addEmailOptions({reply_to_id: model.get('id')});
            });
        }
    },

    /**
     * Returns the recipients to use in the To field of the email.
     *
     * If `this.def.reply_all` is true, then the recipients in the To field
     * from the original email are included.
     *
     * @see EmailClientLaunch plugin.
     * @param {Data.Bean} model Use this model when identifying the recipients.
     * @return {undefined|Array}
     */
    emailOptionTo: function(model) {
        var originalTo;
        var originalSender = model.get('from_collection');
        var to = this._createRecipients(originalSender);

        if (this.def.reply_all) {
            originalTo = model.get('to_collection');
            to = _.union(to, this._createRecipients(originalTo));
        }

        return to;
    },

    /**
     * Returns the recipients to use in the CC field of the email, if
     * `this.def.reply_all` is true. These recipients are the same ones who
     * appeared in the original email's CC field.
     *
     * @see EmailClientLaunch plugin.
     * @param {Data.Bean} model Use this model when identifying the recipients.
     * @return {undefined|Array}
     */
    emailOptionCc: function(model) {
        var originalCc;
        var cc;

        if (this.def.reply_all) {
            originalCc = model.get('cc_collection');
            cc = this._createRecipients(originalCc);
        }

        return cc;
    },

    /**
     * Returns the subject to use in the email.
     *
     * @see EmailClientLaunch plugin.
     * @param {Data.Bean} model Use this model when constructing the subject.
     * @return {undefined|string}
     */
    emailOptionSubject: function(model) {
        var subject = this._getReplySubject(model.get('name'));

        return subject;
    },

    /**
     * Returns the plain-text body to use in the email.
     *
     * @see EmailClientLaunch plugin.
     * @param {Data.Bean} model Use this model when constructing the body.
     * @return {undefined|string}
     */
    emailOptionDescription: function(model) {
        var headerParams;
        var replyHeader;
        var replyBody;
        var description;

        if (!this.useSugarEmailClient()) {
            headerParams = this._getReplyHeaderParams(model);
            replyHeader = this._getReplyHeader(headerParams);
            replyBody = this._getReplyBody(model);
            description = '\n' + replyHeader + '\n' + replyBody;
        }

        return description;
    },

    /**
     * Returns the HTML body to use in the email.
     *
     * @see EmailClientLaunch plugin.
     * @param {Data.Bean} model Use this model when constructing the body.
     * @return {undefined|string}
     */
    emailOptionDescriptionHtml: function(model) {
        var tplHeaderHtml = this._getHeaderHtmlTemplate();
        var headerParams = this._getReplyHeaderParams(model);
        var replyHeaderHtml = tplHeaderHtml(headerParams);
        var replyBodyHtml = this._getReplyBodyHtml(model);
        // The "replycontent" ID is added to the div wrapper around the reply
        // content for later identifying the portion of the email body which is
        // the reply content (e.g., when inserting templates into an email, but
        // maintaining the reply content).
        var descriptionHtml = '<div></div><div id="replycontent">' + replyHeaderHtml + replyBodyHtml + '</div>';

        return descriptionHtml;
    },

    /**
     * Returns the bean to use as the email's related record.
     *
     * @see EmailClientLaunch plugin.
     * @param {Data.Bean} model This model's parent is used as the email's
     * related record.
     * @return {undefined|Data.Bean}
     */
    emailOptionRelated: function(model) {
        var parent;

        if (model.get('parent') && model.get('parent').type && model.get('parent').id) {
            // We omit type because it is actually the module name and should
            // not be treated as an attribute.
            parent = app.data.createBean(model.get('parent').type, _.omit(model.get('parent'), 'type'));
        } else if (model.get('parent_type') && model.get('parent_id')) {
            parent = app.data.createBean(model.get('parent_type'), {
                id: model.get('parent_id'),
                name: model.get('parent_name')
            });
        }

        return parent;
    },

    /**
     * Returns the teamset array to seed the email's teams.
     *
     * @see EmailClientLaunch plugin.
     * @param {Data.Bean} model This model's teams is used as the email's
     * teams.
     * @return {undefined|Array}
     */
    emailOptionTeams: function(model) {
        return model.get('team_name');
    },

    /**
     * Sets up the email options for the EmailClientLaunch plugin to use -
     * passing to the email compose drawer or building up the mailto link.
     *
     * @protected
     * @deprecated The EmailClientLaunch plugin handles email options.
     */
    _updateEmailOptions: function() {
        app.logger.warn('View.Fields.Base.Emails.ReplyActionField#_updateEmailOptions is deprecated. ' +
            'The EmailClientLaunch plugin handles email options.');
    },

    /**
     * Return the HTML reply header template if it exists, or retrieves it.
     *
     * @return {Function}
     * @private
     */
    _getHeaderHtmlTemplate: function() {
        // Use `this.def.type` because `this.type` was changed to `emailaction`
        // during initialization.
        this._tplHeaderHtml = this._tplHeaderHtml ||
            app.template.getField(this.def.type, 'reply-header-html', this.module);
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
     * @deprecated Use
     * View.Fields.Base.Emails.ReplyActionField#emailOptionTo and
     * View.Fields.Base.Emails.ReplyActionField#emailOptionCc instead.
     */
    _getReplyRecipients: function(all) {
        app.logger.warn('View.Fields.Base.Emails.ReplyActionField#_getReplyRecipients is deprecated. Use ' +
            'View.Fields.Base.Emails.ReplyActionField#emailOptionTo and ' +
            'View.Fields.Base.Emails.ReplyActionField#emailOptionCc instead.');

        return {
            to: this.emailOptionTo(this.model) || [],
            cc: this.emailOptionCc(this.model) || []
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
     * @param {Data.Bean} model The params come from this model's attributes.
     * EmailClientLaunch plugin should dictate the model based on the context.
     * @return {Object}
     * @protected
     */
    _getReplyHeaderParams: function(model) {
        // Falls back to the `this.model` for backward compatibility.
        model = model || this.model;

        return {
            from: this._formatEmailList(model.get('from_collection')),
            date: model.get('date_sent'),
            to: this._formatEmailList(model.get('to_collection')),
            cc: this._formatEmailList(model.get('cc_collection')),
            name: model.get('name')
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
     * Given a list of people, format a text only list for use in a reply
     * header.
     *
     * @param {Data.BeanCollection} collection A list of models
     * @protected
     */
    _formatEmailList: function(collection) {
        return collection.map(function(model) {
            var name = model.get('parent_name') || '';

            if (_.isEmpty(name)) {
                return model.get('email_address') || '';
            }

            if (_.isEmpty(model.get('email_address'))) {
                return name;
            }

            return name + ' <' + model.get('email_address') + '>';
        }).join(', ');
    },

    /**
     * Create an array of email recipients from the collection, which can be
     * used as recipients to pass to the new email.
     *
     * @param {Data.BeanCollection} collection
     * @return {Array}
     * @private
     */
    _createRecipients: function(collection) {
        return collection.map(function(recipient) {
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
    },

    /**
     * Retrieve the plain text version of the reply body.
     *
     * @param {Data.Bean} model The body should come from this model's
     * attributes. EmailClientLaunch plugin should dictate the model based on
     * the context.
     * @return {string} The reply body
     * @private
     */
    _getReplyBody: function(model) {
        // Falls back to the `this.model` for backward compatibility.
        model = model || this.model;

        return model.get('description') || '';
    },

    /**
     * Retrieve the HTML version of the reply body.
     *
     * Ensure the result is a defined string and strip any signature wrapper
     * tags to ensure it doesn't get stripped if we insert a signature above
     * the reply content. Also strip any reply content class if this is a
     * reply to a previous reply.
     *
     * @param {Data.Bean} model The body should come from this model's
     * attributes. EmailClientLaunch plugin should dictate the model based on
     * the context.
     * @return {string} The reply body
     * @private
     */
    _getReplyBodyHtml: function(model) {
        var body;

        // Falls back to the `this.model` for backward compatibility.
        model = model || this.model;

        body = model.get('description_html') || '';
        body = body.replace('<div class="signature">', '<div>');
        body = body.replace('<div id="replycontent">', '<div>');

        return body;
    }
})
