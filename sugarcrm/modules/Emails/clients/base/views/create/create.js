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
 * @class View.Views.Base.Emails.CreateView
 * @alias SUGAR.App.view.views.BaseEmailsCreateView
 * @extends View.Views.Base.CreateView
 */
({
    extendsFrom: 'CreateView',

    MIN_EDITOR_HEIGHT: 300,
    EDITOR_RESIZE_PADDING: 5,
    ATTACHMENT_FIELD_HEIGHT: 44,

    STATE_DRAFT: 'Draft',
    STATE_READY: 'Ready',

    //Constants dealing with where to insert content into the email body
    ABOVE_CONTENT: 'above',
    BELOW_CONTENT: 'below',
    CURSOR_LOCATION: 'cursor',

    sendButtonName: 'send_button',
    cancelButtonName: 'cancel_button',
    saveAsDraftButtonName: 'draft_button',

    /**
     * Keep track of the last selected signature so it can be re-inserted in
     * the case where a template is inserted.
     *
     * @property {Object}
     */
    _lastSelectedSignature: null,

    /**
     * @property {RegExp}
     * Used for determining if an email's content contains variables.
     */
    _hasVariablesRegex: /\$[a-zA-Z]+_[a-zA-Z0-9_]+/,

    /**
     * @property {boolean}
     * False when the email client reports a configuration issue
     */
    _userHasConfiguration: true,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        var recipientsField;
        var num;

        this._super('initialize', [options]);

        this.context.on('tinymce:selected_signature:clicked', this._insertSignatureAtCursor, this);
        this.context.on('tinymce:template:clicked', this._launchTemplateDrawer, this);
        this.context.on('tinymce:oninit', this.handleTinyMceInit, this);
        this.context.on('recipients-email:resize-editor', this.resizeEditor, this);
        this.model.on('change:attachments', function() {
            this._setAttachmentVisibility();
            this._checkAttachmentLimit();
        }, this);
        this.on('more-less:toggled', this.handleMoreLessToggled, this);
        this.on('email_not_configured', this.notifyConfigurationStatus, this);
        this.listenTo(this.model, 'change:to', this._renderRecipientsField);
        this.listenTo(this.model, 'change:cc', this._renderRecipientsField);
        this.listenTo(this.model, 'change:bcc', this._renderRecipientsField);

        recipientsField = this.getFieldMeta('recipients');
        num = _.size(recipientsField.fields);

        this.on('email-recipients:loading', function() {
            this.toggleButtons(false);
        }, this);

        this.on('email-recipients:loaded', function() {
            num--;
            if (num === 0) {
                this.toggleButtons(true);
            }
        }, this);

        app.drawer.on('drawer:resize', this.resizeEditor, this);

        this._initializeDefaultSignature();
    },

    /**
     * Set the default signature as the last selected signature for later
     * insertion.
     *
     * @protected
     */
    _initializeDefaultSignature: function() {
        var defaultSignature;

        defaultSignature = app.user.getPreference('signature_default');
        if (!(defaultSignature instanceof app.Bean)) {
            defaultSignature = app.data.createBean('UserSignatures', defaultSignature);
        }
        this._lastSelectedSignature = defaultSignature;
    },

    /**
     * @inheritdoc
     * The EmailsApi is using a 451 http status code to report those errors for which the desired behavior is to
     * display (return) an appropriate error message to end user and provide useful information as to what the
     * issue is that was encountered such that it can be corrected. Existing errors in the 400-499 range are being
     * haandled by core sugar code at a level that prevents these errors to be handled by Email compose because it is
     * in a drawer, a limitation that we expect to address in the future.
     */
    saveModel: function(success, error) {
        var onError = _.bind(function(model, e) {
            if (e && e.status == 451) {
                // Mark the error as having been handled
                e.handled = true;
                this.enableButtons();
                app.alert.show(e.error, {
                    level: 'error',
                    autoClose: false,
                    messages: e.message
                });
            } else if (error) {
                error(model, e);
            }
        }, this);

        this._super('saveModel', [success, onError]);
    },

    /**
     * @inheritdoc
     */
    delegateButtonEvents: function() {
        this.context.on('button:' + this.sendButtonName + ':click', this.send, this);
        this.context.on('button:' + this.saveAsDraftButtonName + ':click', this.saveAsDraft, this);
        this.context.on('button:' + this.cancelButtonName + ':click', this.cancel, this);
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        var prepopulateValues;

        this._super('_render');

        this.setTitle(app.lang.get('LBL_COMPOSEEMAIL', this.module));

        prepopulateValues = this.context.get('prepopulate');
        if (!_.isEmpty(prepopulateValues)) {
            this.prepopulate(prepopulateValues);
        }

        if (this.model.isNew()) {
            this._signatureLocation = this._signatureLocation || this.BELOW_CONTENT;
            this._insertSignature(this._lastSelectedSignature, this._signatureLocation);
        }

        this._setAttachmentVisibility();
    },

    /**
     * Render recipients fieldset
     */
    _renderRecipientsField: _.debounce(function() {
        var field = this.getField('recipients');
        if (field) {
            field.render();
        }
    }, 200),

    /**
     * @inheritdoc
     *
     * `BaseEmailsCreateView` is used when creating new emails and editing
     * existing drafts. The model is not new when editing drafts. In those
     * cases, {@link BaseEmailsRecordView#hasUnsavedChanges} is called to use
     * logic that checks for unsaved changes for existing records instead of
     * new records.
     */
    hasUnsavedChanges: function() {
        if (this.model.isNew()) {
            return this._super('hasUnsavedChanges');
        }

        return app.view.views.BaseEmailsRecordView.prototype.hasUnsavedChanges.call(this);
    },

    /**
     * This method is called when the view is notified that email has not been
     * configured. Disables send button.
     *
     * @param {HttpError} error
     */
    notifyConfigurationStatus: function(error) {
        var sendButton = this.getField('send_button');

        if (sendButton) {
            sendButton.setDisabled(true);
        }

        this._userHasConfiguration = false;
    },

    /**
     * Prepopulate fields on the email compose screen that are passed in on the
     * context when opening this view
     * TODO: Refactor once we have custom module specific models
     * @param {Object} values
     */
    prepopulate: function(values) {
        var self = this;
        var fields = app.metadata.getModule(this.module, 'fields');
        fields = _.keys(fields);
        _.defer(function() {
            _.each(values, function(value, fieldName) {
                switch (fieldName) {
                    case 'related':
                        self._populateForModules(value);
                        self._populateRelated(value);
                        break;
                    case 'to':
                    case 'cc':
                    case 'bcc':
                    case 'attachments':
                        self.model.get(fieldName).add(value);
                        break;
                    case '_signatureLocation':
                        self._signatureLocation = value;
                        break;
                    default:
                        if (_.contains(fields, fieldName)) {
                            self.model.set(fieldName, value);
                        }
                }
            });

            //Restore the signature if setting body content and a default
            //signature exists
            if (values.description_html && self._lastSelectedSignature) {
                self._insertSignature(self._lastSelectedSignature, self._signatureLocation);
            }

            //Reply has special requirement to focus editor at top of content
            if (!_.isEmpty(values.reply_to_id)) {
                self._focusEditor();
            }
        });
    },

    /**
     * Populates email compose with module specific data.
     * TODO: Refactor once we have custom module specific models
     * @param {Data.Bean} relatedModel
     */
    _populateForModules: function(relatedModel) {
        if (relatedModel.module === 'Cases') {
            this._populateForCases(relatedModel);
        }
    },

    /**
     * Populates email compose with cases specific data.
     * TODO: Refactor once we have custom module specific models
     * @param {Data.Bean} relatedModel
     */
    _populateForCases: function(relatedModel) {
        var config = app.metadata.getConfig();
        var keyMacro = '%1';
        var caseMacro = config.inboundEmailCaseSubjectMacro;
        var subject = caseMacro + ' ' + relatedModel.get('name');
        var contacts;

        subject = subject.replace(keyMacro, relatedModel.get('case_number'));
        this.model.set('name', subject);
        if (!this.isFieldPopulated('to')) {
            // no addresses, attempt to populate from contacts relationship
            contacts = relatedModel.getRelatedCollection('contacts');

            contacts.fetch({
                relate: true,
                success: _.bind(function(data) {
                    if (data.models && data.models.length > 0) {
                        this.model.get('to').add(data.models);
                    }
                }, this),
                fields: ['id', 'full_name', 'email']
            });
        }
    },

    /**
     * Populate the parent_name (type: parent) with the related record passed in
     *
     * @param {Data.Bean} relatedModel
     */
    _populateRelated: function(relatedModel) {
        var setParent = _.bind(function(model) {
            var parentNameField;

            if (this.disposed) {
                return;
            }

            parentNameField = this.getField('parent_name');
            if (model.module && parentNameField.isAvailableParentType(model.module)) {
                model.value = model.get('name');
                parentNameField.setValue(model);
            }
        }, this);

        if (!_.isEmpty(relatedModel.get('id')) && !_.isEmpty(relatedModel.get('name'))) {
            setParent(relatedModel);
        } else if (!_.isEmpty(relatedModel.get('id'))) {
            relatedModel.fetch({
                showAlerts: false,
                success: _.bind(function(relatedModel) {
                    setParent(relatedModel);
                }, this),
                fields: ['name']
            });
        }
    },

    /**
     * Get the individual related object fields from the model and format for the API
     *
     * @return {Object} API related argument as array with appropriate fields set
     */
    getRelatedForApi: function() {
        var related = {};
        var id = this.model.get('parent_id');
        var type;

        if (!_.isUndefined(id)) {
            id = id.toString();
            if (id.length > 0) {
                related.id = id;
                type = this.model.get('parent_type');
                if (!_.isUndefined(type)) {
                    type = type.toString();
                }
                related.type = type;
            }
        }

        return related;
    },

    /**
     * Get the team information from the model and format for the API
     *
     * @return {Object} API teams argument as array with appropriate fields set
     */
    getTeamsForApi: function() {
        var teamName = this.model.get('team_name') || [];
        var teams = {};
        teams.others = [];

        if (!_.isArray(teamName)) {
            teamName = [teamName];
        }

        _.each(teamName, function(team) {
            if (team.primary) {
                teams.primary = team.id.toString();
            } else if (!_.isUndefined(team.id)) {
                teams.others.push(team.id.toString());
            }
        }, this);

        if (teams.others.length == 0) {
            delete teams.others;
        }

        return teams;
    },

    /**
     * Build a backbone model that will be sent to the Mail API
     */
    initializeSendEmailModel: function() {
        var sendModel = new Backbone.Model(_.extend({}, this.model.attributes, {
            related: this.getRelatedForApi(),
            teams: this.getTeamsForApi()
        }));
        return sendModel;
    },

    /**
     * @inheritdoc
     *
     * Verify that the email does not have any invalid recipients before continuing with the save
     */
    save: function() {
        if (this._hasInvalidRecipients(this.model)) {
            app.alert.show('mail_invalid_recipients', {
                level: 'error',
                messages: app.lang.get('ERR_INVALID_RECIPIENTS', this.module)
            });
            return;
        }

        this._super('save');
    },

    /**
     * Save the email as a draft for later sending
     */
    saveAsDraft: function() {
        this.model.set('state', this.STATE_DRAFT);
        this.save();
    },

    /**
     * Save and close drawer
     */
    saveAndClose: function() {
        this.initiateSave(_.bind(function() {
            var currentRoute;
            var newRoute;

            if (this.closestComponent('drawer')) {
                app.drawer.close(this.context, this.model);
            } else if (this.model.get('state') === 'Draft') {
                // redirect to list view
                newRoute = app.router.buildRoute(this.module);
                app.router.navigate(newRoute, {trigger: true});
            } else {
                currentRoute = Backbone.history.getFragment();
                newRoute = app.router.buildRoute(this.module, this.model.id);
                (currentRoute === newRoute) ? app.router.refresh() : app.router.navigate(newRoute, {trigger: true});
            }
        }, this));
    },

    /**
     * Send the email immediately or warn if user did not provide subject or body
     */
    send: function() {
        var confirmationMessages = '';
        var showConfirmation = false;
        var fullContent = '';

        var sendEmail = _.bind(function() {
            this.model.set('state', this.STATE_READY);
            this.save();
        }, this);

        if (!this.isFieldPopulated('to') &&
            !this.isFieldPopulated('cc') &&
            !this.isFieldPopulated('bcc')
        ) {
            this.model.trigger('error:validation:to');
            app.alert.show('send_error', {
                level: 'error',
                messages: 'LBL_EMAIL_COMPOSE_ERR_NO_RECIPIENTS'
            });
        } else {
            // to/cc/bcc filled out, check other fields
            if (!this.isFieldPopulated('name') && !this.isFieldPopulated('description_html')) {
                confirmationMessages += app.lang.get('LBL_NO_SUBJECT_NO_BODY_SEND_ANYWAYS', this.module) + '<br />';
                showConfirmation = true;
            } else if (!this.isFieldPopulated('name')) {
                confirmationMessages += app.lang.get('LBL_SEND_ANYWAYS', this.module) + '<br />';
                showConfirmation = true;
            } else if (!this.isFieldPopulated('description_html')) {
                confirmationMessages += app.lang.get('LBL_NO_BODY_SEND_ANYWAYS', this.module) + '<br />';
                showConfirmation = true;
            }

            fullContent = this._getFullContent();

            if (_.isEmptyValue(this.model.get('parent_id')) && this._hasVariablesRegex.test(fullContent)) {
                confirmationMessages += app.lang.get('LBL_NO_RELATED_TO_WITH_TEMPLATE_SEND_ANYWAYS', this.module);
                showConfirmation = true;
            }

            if (showConfirmation) {
                app.alert.show('send_confirmation', {
                    level: 'confirmation',
                    messages: confirmationMessages,
                    onConfirm: sendEmail
                });
            } else {
                // All checks pass, send the email
                sendEmail();
            }
        }
    },

    /**
     * @inheritdoc
     *
     * Build the appropriate success message based on the state of the email.
     */
    buildSuccessMessage: function() {
        var successLabel = (this.model.get('state') === this.STATE_DRAFT) ?
            'LBL_DRAFT_SAVED' :
            'LBL_EMAIL_SENT';

        return app.lang.get(successLabel, this.module);
    },

    /**
     * Is this field populated?
     * @param {string} fieldName
     * @return {boolean}
     */
    isFieldPopulated: function(fieldName) {
        var value = this.model.get(fieldName);

        if (value instanceof Backbone.Collection) {
            return value.length !== 0;
        } else {
            if (_.isEqual(fieldName, 'description_html')) {
                // When fetching tinyMCE content, convert to jQuery Object
                // and return only if text is not empty. By wrapping the value
                // in <div> tags we remove the error if the value contains
                // no HTML markup
                value = value || '';
                return !_.isEmpty($.trim($('<div>' + value + '</div>').text()));
            } else {
                return !_.isEmpty($.trim(value));
            }
        }
    },

    /**
     * Check if the recipients in any of the recipient fields are invalid.
     *
     * @param {Backbone.Model} model
     * @return {boolean} Return true if there are invalid recipients in any of
     *   the fields. Return false otherwise.
     * @private
     */
    _hasInvalidRecipients: function(model) {
        return _.some(['from', 'to', 'cc', 'bcc'], function(fieldName) {
            var recipients = model.get(fieldName);
            if (!recipients) {
                return false;
            }
            return _.some(recipients.models, function(recipient) {
                return recipient.get('_invalid');
            });
        }, this);
    },

    /**
     * Concatenates all content attributes: The subject, plain-text and HTML
     * parts.
     *
     *
     * @return {string}
     * @private
     */
    _getFullContent: function() {
        var subject = this.model.get('name') || '';
        var text = this.model.get('description') || '';
        var html = this.model.get('description_html') || '';
        return subject + text + html;
    },

    /**
     * Open the drawer with the EmailTemplates selection list layout. The callback should take the data passed to it
     * and replace the existing editor contents with the selected template.
     */
    _launchTemplateDrawer: function() {
        app.drawer.open({
                layout: 'selection-list',
                context: {
                    module: 'EmailTemplates',
                    fields: [
                        'subject',
                        'body',
                        'body_html',
                        'text_only'
                    ]
                }
            },
            _.bind(this._templateDrawerCallback, this)
        );
    },

    /**
     * Receives the selected template to insert and begins the process of confirming the operation and inserting the
     * template into the editor.
     *
     * @param {Data.Bean} model
     */
    _templateDrawerCallback: function(model) {
        var emailTemplate;

        // This is an edge case where user has List but not View permission.
        // Search & Select will return only id and name if View permission is
        // not permitted for this record. Display appropriate error.
        if (model && _.isUndefined(model.subject)) {
            app.alert.show('no_access_error',
                {
                    level: 'error',
                    messages: app.lang.get('ERR_NO_ACCESS', this.module, {name: model.value})
                }
            );
            return;
        }

        if (model) {
            emailTemplate = app.data.createBean('EmailTemplates', model);
            this._confirmTemplate(emailTemplate);
        }
    },

    /**
     * Presents the user with a confirmation prompt indicating that inserting the template will replace all content
     * in the editor. If the user confirms "yes" then the template will inserted.
     *
     * @param {Data.Bean} template
     */
    _confirmTemplate: function(template) {
        //if view is already disposed, bail out
        if (this.disposed === true) {
            return;
        }

        if (_.isEmpty(this._getFullContent())) {
            this._insertTemplate(template);
        } else {
            app.alert.show('delete_confirmation', {
                level: 'confirmation',
                messages: app.lang.get('LBL_EMAILTEMPLATE_MESSAGE_SHOW_MSG', this.module),
                onConfirm: _.bind(function(event) {
                    // Track click on confirmation button.
                    app.analytics.trackEvent('click', 'email_template_confirm', event);
                    this._insertTemplate(template);
                }, this),
                onCancel: function(event) {
                    // Track click on cancel button.
                    app.analytics.trackEvent('click', 'email_template_cancel', event);
                }
            });
        }
    },

    /**
     * Inserts the template into the editor.
     *
     * @param {Data.Bean} template
     */
    _insertTemplate: function(template) {
        var subject;
        var replyContent;

        if (_.isObject(template)) {
            // Track applying an email template.
            app.analytics.trackEvent('email_template', 'apply', template);

            replyContent = this._getReplyContent();
            subject = template.get('subject');

            //Only use the subject if its not a reply
            if (subject && !replyContent) {
                this.model.set('name', subject);
            }

            //TODO: May need to move over replaces special characters.
            if (template.get('text_only')) {
                this.model.set('description_html', template.get('body'));
            } else {
                this.model.set('description_html', template.get('body_html'));
            }

            this.trigger('email_attachments:template:add', template);

            if (this._lastSelectedSignature) {
                // currently adds the html signature even when the template is text-only
                this._insertSignature(this._lastSelectedSignature, this.BELOW_CONTENT);
            }

            if (replyContent) {
                this._insertInEditor(replyContent, this.BELOW_CONTENT);
            }
        }
    },

    /**
     * Check email body and pull out any reply content from a draft email.
     *
     * @return {string} The reply content.
     * @protected
     */
    _getReplyContent: function() {
        var replyContent = '';
        var body = this.model.get('description_html') || '';
        var $replyContent = $('<div>' + body + '</div>').find('div#replycontent');

        if ($replyContent.length > 0) {
            replyContent = $replyContent[0].outerHTML;
        }
        return replyContent;
    },

    /**
     * Hide attachment field row if no attachments, show when added
     */
    _setAttachmentVisibility: function() {
        var field = this.getField('attachments');
        var $el;
        var $row;

        if (!field) {
            return;
        }

        $el = field.getFieldElement();
        $row = $el.closest('.row-fluid');

        if (field.isEmpty()) {
            $row.addClass('hidden');
            $row.removeClass('single');
        } else {
            $row.removeClass('hidden');
            $row.addClass('single');
        }

        this.resizeEditor();
    },

    /**
     * Calculate the sum total bytes from each attachment
     * associated with the email
     *
     * @return {number}
     * @private
     */
    _calculateTotalAttachments: function() {
        return this.model.get('attachments').reduce(function(memo, attachment) {
            var fileSize = attachment.get('file_size');

            if (!_.isNumber(fileSize)) {
                try {
                    fileSize = parseInt(fileSize, 10);
                } catch (err) {
                    // If failed conversion, treat attachment as 0 filesize
                    fileSize = 0;
                }
            }

            return memo + fileSize;
        }, 0);
    },

    /**
     * Enable/disable the draft/send buttons based on if the sum total of the
     * attachments exceeds the maximum
     *
     * @private
     */
    _checkAttachmentLimit: function() {
        var totalBytes = this._calculateTotalAttachments();
        var maxTotalSize = app.config.maxAggregateEmailAttachmentsBytes;
        var sendButton = this.getField(this.sendButtonName);
        var draftButton = this.getField(this.saveAsDraftButtonName);
        var readableMax = app.utils.getReadableFileSize(maxTotalSize);
        var label = app.lang.get('LBL_TOTAL_ATTACHMENT_MAX_SIZE', this.module);

        if (totalBytes > maxTotalSize) {
            app.alert.show('email-attachment-status', {
                level: 'warning',
                messages: app.utils.formatString(label, [readableMax])
            });

            if (sendButton) {
                sendButton.setDisabled(true);
            }
            if (draftButton) {
                draftButton.setDisabled(true);
            }
        } else {
            if (sendButton) {
                sendButton.setDisabled(!this._userHasConfiguration);
            }
            if (draftButton) {
                draftButton.setDisabled(false);
            }

            app.alert.dismiss('email-attachment-status');
        }
    },

    /**
     * Inserts the signature at the current cursor location in the editor.
     *
     * @param {string} signature
     * @private
     */
    _insertSignatureAtCursor: function(signature) {
        this._insertSignature(signature, this.CURSOR_LOCATION);
    },

    /**
     * Inserts the signature into the editor.
     *
     * @param {Data.Bean} signature
     * @param {string} [location="cursor"] Whether to insert the new content
     *   above existing content, below existing content, or at the cursor
     *   location. Defaults to being inserted at the cursor position.
     * @return {boolean}
     * @private
     */
    _insertSignature: function(signature, location) {
        var htmlBodyObj;
        var emailBody;
        var signatureOpenTag;
        var signatureCloseTag;
        var formattedSignature;
        var signatureContent;

        if (_.isObject(signature) && signature.get('signature_html')) {
            signatureOpenTag = '<div class="signature keep">';
            signatureCloseTag = '</div>';
            formattedSignature = this._formatSignature(signature.get('signature_html'));
            signatureContent = signatureOpenTag + formattedSignature + signatureCloseTag;

            emailBody = this._insertInEditor(signatureContent, location);
            htmlBodyObj = $('<div>' + emailBody + '</div>');

            // Mark each signature to either keep or remove
            $('div.signature', htmlBodyObj).each(function() {
                if (!$(this).hasClass('keep')) {
                    // Mark for removal
                    $(this).addClass('remove');
                } else {
                    // if the parent is also a signature, move node out of the parent so it isn't removed
                    if ($(this).parent().hasClass('signature')) {
                        // Move the signature outside of the nested signature
                        $(this).parent().before(this);
                    }
                    // Remove the "keep" class so if another signature is added it will remove this one
                    $(this).removeClass('keep');
                }
            });

            // After each signature is marked, perform the removal
            htmlBodyObj.find('div.signature.remove').remove();

            emailBody = htmlBodyObj.html();
            this.model.set('description_html', emailBody);

            this._lastSelectedSignature = signature;
            return true;
        }

        return false;
    },

    /**
     * Inserts the content into the tinyMCE editor at the specified location.
     *
     * @param {string} content
     * @param {string} [location="cursor"] Whether to insert the new content
     *   above existing content, below existing content, or at the cursor
     *   location. Defaults to being inserted at the cursor position.
     * @return {string} the content of the editor
     * @private
     */
    _insertInEditor: function(content, location) {
        var emailBody = this.model.get('description_html') || '';
        var editor;

        if (_.isEmpty(content)) {
            return emailBody;
        }

        //Default to the cursor location
        location = location || this.CURSOR_LOCATION;

        //Add empty divs so user can place cursor on line before or after
        content = '<div></div>' + content + '<div></div>';

        if (location === this.CURSOR_LOCATION) {
            editor = this.getField('description_html').getEditor();

            //if no editor, not able to insert at cursor
            if (_.isNull(editor)) {
                return emailBody;
            }

            editor.execCommand('mceInsertContent', false, content);

            emailBody = editor.getContent();
        } else if (location === this.BELOW_CONTENT) {
            emailBody += content;
        } else if (location === this.ABOVE_CONTENT) {
            emailBody = content + emailBody;
        }

        this.model.set('description_html', emailBody);

        return emailBody;
    },

    /**
     * Formats HTML signatures to replace select HTML-entities with their true characters.
     *
     * @param {string} signature
     */
    _formatSignature: function(signature) {
        signature = signature.replace(/&lt;/gi, '<');
        signature = signature.replace(/&gt;/gi, '>');

        return signature;
    },

    /**
     * Show a generic alert for server errors resulting from custom API calls during Email Compose workflows. Logs
     * the error message for system administrators as well.
     *
     * @param {SUGAR.HttpError} error
     * @private
     */
    _showServerError: function(error) {
        app.alert.show('server-error', {
            level: 'error',
            messages: 'ERR_GENERIC_SERVER_ERROR'
        });
        app.error.handleHttpError(error);
    },

    /**
     * When toggling to show/hide hidden panel, resize editor accordingly
     */
    handleMoreLessToggled: function() {
        this.resizeEditor();
    },

    /**
     * When TinyMCE has been completely initialized, go ahead and resize the editor
     */
    handleTinyMceInit: function() {
        this.resizeEditor();
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        if (app.drawer) {
            app.drawer.off(null, null, this);
        }
        app.alert.dismiss('email-client-status');
        this._super('_dispose');
    },

    /**
     * Resize the html editor based on height of the drawer it is in
     *
     * @param {number} [drawerHeight] current height of the drawer or height the drawer will be after animations
     */
    resizeEditor: function(drawerHeight) {
        var $editor;
        var headerHeight;
        var recordHeight;
        var showHideHeight;
        var diffHeight;
        var editorHeight;
        var newEditorHeight;

        $editor = this.$('.mce-stack-layout .mce-stack-layout-item iframe');
        //if editor not already rendered, cannot resize
        if ($editor.length === 0) {
            return;
        }

        drawerHeight = drawerHeight || app.drawer.getHeight();
        headerHeight = this.$('.headerpane').outerHeight(true);
        recordHeight = this.$('.record').outerHeight(true);
        showHideHeight = this.$('.show-hide-toggle').outerHeight(true);
        editorHeight = $editor.height();

        //calculate the space left to fill - subtracting padding to prevent scrollbar
        diffHeight = drawerHeight - headerHeight - recordHeight - showHideHeight -
            this.ATTACHMENT_FIELD_HEIGHT - this.EDITOR_RESIZE_PADDING;

        //add the space left to fill to the current height of the editor to get a new height
        newEditorHeight = editorHeight + diffHeight;

        //maintain min height
        if (newEditorHeight < this.MIN_EDITOR_HEIGHT) {
            newEditorHeight = this.MIN_EDITOR_HEIGHT;
        }

        //set the new height for the editor
        $editor.height(newEditorHeight);
    },

    /**
     * Focus the email body editor at the top of the content.
     *
     * @return {boolean}
     * @private
     */
    _focusEditor: function() {
        var editor = this.getField('description_html').getEditor();

        if (!editor) {
            //Editor not initialized yet, retry when it is intialized
            this.context.once('tinymce:oninit', this._focusEditor, this);
            return false;
        }

        $(editor).focus();
        editor.selection.select(editor.getBody(), true);
        editor.selection.collapse(true);
        return true;
    }
})
