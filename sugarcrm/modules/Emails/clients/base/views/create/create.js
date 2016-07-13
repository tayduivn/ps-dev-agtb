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
    FIELD_PANEL_BODY_SELECTOR: '.row-fluid.panel_body',

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
     * Keep track of the reply content so it can be re-inserted in the case
     * where a template is inserted.
     *
     * @property {string}
     */
    _replyContent: null,

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
        var defaultSignature;

        this._super('initialize', [options]);
        this.events = _.extend({}, this.events, {
            'click [data-toggle-field]': '_handleRecipientOptionClick'
        });
        this.context.on('tinymce:selected_signature:clicked', this._insertSignatureAtCursor, this);
        this.context.on('tinymce:template:clicked', this._launchTemplateDrawer, this);
        this.context.on('tinymce:oninit', this.handleTinyMceInit, this);
        this.model.on('change:attachments', function() {
            this._setAttachmentVisibility();
            this._checkAttachmentLimit();
        }, this);
        this.on('more-less:toggled', this.handleMoreLessToggled, this);
        app.drawer.on('drawer:resize', this.resizeEditor, this);

        //Set the default signature as the last selected signature for later
        //insertion.
        defaultSignature = app.user.getPreference('signature_default');
        if (!(defaultSignature instanceof app.Bean)) {
            defaultSignature = app.data.createBean('UserSignatures', defaultSignature);
        }
        this._lastSelectedSignature = defaultSignature;
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
        var $controls;
        var prepopulateValues;

        this._super('_render');

        $controls = this.$('.control-group:not(.hide) .control-label');
        if ($controls.length) {
            $controls.first().addClass('begin-fieldgroup');
            $controls.last().addClass('end-fieldgroup');
        }

        this.setTitle(app.lang.get('LBL_COMPOSEEMAIL', this.module));

        prepopulateValues = this.context.get('prepopulate');
        if (!_.isEmpty(prepopulateValues)) {
            this.prepopulate(prepopulateValues);
        }
        this._addRecipientOptions();

        if (this.model.isNew()) {
            this._signatureLocation = this._signatureLocation || this.BELOW_CONTENT;
            this._insertSignature(this._lastSelectedSignature, this._signatureLocation);
        }

        this._setAttachmentVisibility();

        this.notifyConfigurationStatus();
    },

    /**
     * Notifies the user of configuration issues and disables send button
     */
    notifyConfigurationStatus: function() {
        var sendButton;
        var emailClientPrefence = app.user.getPreference('email_client_preference');

        if (_.isObject(emailClientPrefence) && _.isObject(emailClientPrefence.error)) {
            app.alert.show('email-client-status', {
                level: 'warning',
                messages: app.lang.get(emailClientPrefence.error.message, this.module),
                autoClose: false,
                onLinkClick: function() {
                    app.alert.dismiss('email-client-status');
                }
            });

            this._userHasConfiguration = false;
            sendButton = this.getField('send_button');
            if (sendButton) {
                sendButton.setDisabled(true);
            }
        }
    },

    /**
     * Prepopulate fields on the email compose screen that are passed in on the
     * context when opening this view
     * TODO: Refactor once we have custom module specific models
     * @param {Object} values
     */
    prepopulate: function(values) {
        var self = this;
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
                        self.model.get(fieldName).add(value);
                        break;
                    case '_signatureLocation':
                        self._signatureLocation = value;
                        break;
                    case '_isReply':
                        self._isReply = value;
                        break;
                    default:
                        self.model.set(fieldName, value);
                }

                //Show CC or BCC fields if they are being prepopulated
                if (fieldName === 'cc' || fieldName === 'bcc') {
                    self._initRecipientOption(fieldName);
                }
            });

            //Restore the signature if setting body content and a default
            //signature exists
            if (values.description_html && self._lastSelectedSignature) {
                self._insertSignature(self._lastSelectedSignature, self._signatureLocation);
            }

            if (self._isReply) {
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
     * Add Cc/Bcc toggle buttons
     * Initialize whether to show/hide fields and toggle show/hide buttons appropriately
     */
    _addRecipientOptions: function() {
        this._renderRecipientOptions('to');
        this._initRecipientOption('cc');
        this._initRecipientOption('bcc');
    },

    /**
     * Render the sender option buttons and place them in the given container
     *
     * @param {string} container Name of field that will contain the sender option buttons
     * @private
     */
    _renderRecipientOptions: function(container) {
        var field = this.getField(container);
        var $panelBody;
        var recipientOptionTemplate;

        if (field) {
            $panelBody = field.$el.closest(this.FIELD_PANEL_BODY_SELECTOR);
            recipientOptionTemplate = app.template.getView('create.recipient-options', this.module);

            $(recipientOptionTemplate({'module': this.module}))
                .insertAfter($panelBody.find('div span.normal'));
        }
    },

    /**
     * Check if the given field has a value
     * Hide the field if there is no value prepopulated
     *
     * @param {string} fieldName Name of the field to initialize active state on
     * @private
     */
    _initRecipientOption: function(fieldName) {
        var fieldValue = this.model.get(fieldName) || [];
        this.toggleRecipientOption(fieldName, (fieldValue.length > 0));
    },

    /**
     * Toggle the state of the given field
     * Sets toggle button state and visibility of the field
     *
     * @param {string} fieldName Name of the field to toggle
     * @param {boolean} [active] Whether toggle button active and field shown
     */
    toggleRecipientOption: function(fieldName, active) {
        var toggleButtonSelector = '[data-toggle-field="' + fieldName + '"]';
        var $toggleButton = this.$(toggleButtonSelector);

        // if explicit active state not set, toggle to opposite
        if (_.isUndefined(active)) {
            active = !$toggleButton.hasClass('active');
        }

        $toggleButton.toggleClass('active', active);
        this._toggleFieldVisibility(fieldName, active);
    },

    /**
     * Event Handler for toggling the Cc/Bcc options on the page.
     *
     * @param {Event} event click event
     * @private
     */
    _handleRecipientOptionClick: function(event) {
        var $toggleButton = $(event.currentTarget);
        var fieldName = $toggleButton.data('toggle-field');

        this.toggleRecipientOption(fieldName);
        this.resizeEditor();
    },

    /**
     * Show/hide a field section on the form
     *
     * @param {string} fieldName Name of the field to show/hide
     * @param {boolean} show Whether to show or hide the field
     * @private
     */
    _toggleFieldVisibility: function(fieldName, show) {
        var field = this.getField(fieldName);
        if (field) {
            field.$el.closest(this.FIELD_PANEL_BODY_SELECTOR).toggleClass('hide', !show);
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
                    module: 'EmailTemplates'
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

        if (model) {
            emailTemplate = app.data.createBean('EmailTemplates', {id: model.id});
            emailTemplate.fetch({
                success: _.bind(this._confirmTemplate, this),
                error: _.bind(function(error) {
                    this._showServerError(error);
                }, this)
            });
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
                onConfirm: _.bind(this._insertTemplate, this, template)
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

        if (_.isObject(template)) {
            subject = template.get('subject');

            if (subject) {
                this.model.set('name', subject);
            }

            //TODO: May need to move over replaces special characters.
            if (template.get('text_only') === 1) {
                this.model.set('description_html', template.get('body'));
            } else {
                this.model.set('description_html', template.get('body_html'));
            }

            this.trigger('email_attachments:template:add', template);

            // currently adds the html signature even when the template is text-only
            this._insertSignature(this._lastSelectedSignature, this.BELOW_CONTENT);
        }
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
     * @param {string} location Whether to insert above content, below, or at
     *   the cursor location
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
     * Inserts the content into the tinyMCE editor at the cursor location.
     *
     * @param {string} content
     * @param {string} location Whether to insert above content, below, or at
     *   the cursor location
     * @return {string} the content of the editor
     * @private
     */
    _insertInEditor: function(content, location) {
        var emailBody = this.model.get('description_html') || '';
        var editor = this.getField('description_html').getEditor();

        if (_.isEmpty(content)) {
            //nothing to insert
            return emailBody;
        }

        //Add empty divs so user can place cursor on line before or after
        content = '<div></div>' + content + '<div></div>';

        if (location === this.CURSOR_LOCATION) {
            if (_.isNull(editor)) {
                //no editor, so not able to insert at cursor
                return emailBody;
            }

            editor.execCommand('mceInsertContent', false, content);

            emailBody = editor.getContent();
        } else if (location === this.BELOW_CONTENT) {
            emailBody += content;
        } else {
            emailBody = content + emailBody;
        }

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
