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

    _lastSelectedSignature: null,
    MIN_EDITOR_HEIGHT: 300,
    EDITOR_RESIZE_PADDING: 5,
    ATTACHMENT_FIELD_HEIGHT: 44,
    FIELD_PANEL_BODY_SELECTOR: '.row-fluid.panel_body',

    STATE_DRAFT: 'Draft',
    STATE_READY: 'Ready',

    sendButtonName: 'send_button',
    cancelButtonName: 'cancel_button',
    saveAsDraftButtonName: 'draft_button',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.events = _.extend({}, this.events, {
            'click [data-toggle-field]': '_handleRecipientOptionClick'
        });
        this.context.on('tinymce:selected_signature:clicked', this._updateEditorWithSignature, this);
        this.context.on('tinymce:template:clicked', this._launchTemplateDrawer, this);
        this.context.on('tinymce:oninit', this.handleTinyMceInit, this);
        this.model.on('change:attachments', this._setAttachmentVisibility, this);
        this.on('more-less:toggled', this.handleMoreLessToggled, this);
        app.drawer.on('drawer:resize', this.resizeEditor, this);

        this._lastSelectedSignature = app.user.getPreference('signature_default');
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
        var $controls,
            prepopulateValues;

        this._super('_render');

        $controls = this.$('.control-group:not(.hide) .control-label');
        if ($controls.length) {
            $controls.first().addClass('begin-fieldgroup');
            $controls.last().addClass('end-fieldgroup');
        }

        this.setTitle(app.lang.get('LBL_COMPOSEEMAIL', this.module));

        if (this.model.isNotEmpty) {
            prepopulateValues = this.context.get('prepopulate');
            if (!_.isEmpty(prepopulateValues)) {
                this.prepopulate(prepopulateValues);
            }
            this._addRecipientOptions();

            if (this.model.isNew()) {
                this._updateEditorWithSignature(this._lastSelectedSignature);
            }

            this._setAttachmentVisibility();
        }

        this.notifyConfigurationStatus();
    },

    /**
     * Notifies the user of configuration issues and disables send button
     */
    notifyConfigurationStatus: function() {
        var sendButton,
            emailClientPrefence = app.user.getPreference('email_client_preference');

        if (_.isObject(emailClientPrefence) && _.isObject(emailClientPrefence.error)) {
            app.alert.show('email-client-status', {
                level: 'warning',
                messages: app.lang.get(emailClientPrefence.error.message, this.module),
                autoClose: false,
                onLinkClick: function() {
                    app.alert.dismiss('email-client-status');
                }
            });

            sendButton = this.getField('send_button');
            if (sendButton) {
                sendButton.setDisabled(true);
            }
        }
    },

    /**
     * Prepopulate fields on the email compose screen that are passed in on the context when opening this view
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
                        self.populateRelated(value);
                        break;
                    default:
                        self.model.set(fieldName, value);
                }
            });
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
        var config = app.metadata.getConfig(),
            keyMacro = '%1',
            caseMacro = config.inboundEmailCaseSubjectMacro,
            subject = caseMacro + ' ' + relatedModel.get('name');

        subject = subject.replace(keyMacro, relatedModel.get('case_number'));
        this.model.set('name', subject);
        if (!this.isFieldPopulated('to')) {
            // no addresses, attempt to populate from contacts relationship
            var contacts = relatedModel.getRelatedCollection('contacts');

            contacts.fetch({
                relate: true,
                success: _.bind(function(data) {
                    var toAddresses = _.map(data.models, function(model) {
                        return {bean: model};
                    }, this);

                    this.model.set('to', toAddresses);
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
    populateRelated: function(relatedModel) {
        var setParent = _.bind(function(model) {
            var parentNameField = this.getField('parent_name');
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
        var field = this.getField(container),
            $panelBody,
            recipientOptionTemplate;

        if (field) {
            $panelBody = field.$el.closest(this.FIELD_PANEL_BODY_SELECTOR);
            recipientOptionTemplate = app.template.getView('create.recipient-options', this.module);

            $(recipientOptionTemplate({'module' : this.module}))
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
        var toggleButtonSelector = '[data-toggle-field="' + fieldName + '"]',
            $toggleButton = this.$(toggleButtonSelector);

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
        var $toggleButton = $(event.currentTarget),
            fieldName = $toggleButton.data('toggle-field');

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
                related['id'] = id;
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
     * Send the email immediately or warn if user did not provide subject or body
     */
    send: function() {
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
        } else if (!this.isFieldPopulated('name') && !this.isFieldPopulated('description_html')) {
            app.alert.show('send_confirmation', {
                level: 'confirmation',
                messages: app.lang.get('LBL_NO_SUBJECT_NO_BODY_SEND_ANYWAYS', this.module),
                onConfirm: sendEmail
            });
        } else if (!this.isFieldPopulated('name')) {
            app.alert.show('send_confirmation', {
                level: 'confirmation',
                messages: app.lang.get('LBL_SEND_ANYWAYS', this.module),
                onConfirm: sendEmail
            });
        } else if (!this.isFieldPopulated('description_html')) {
            app.alert.show('send_confirmation', {
                level: 'confirmation',
                messages: app.lang.get('LBL_NO_BODY_SEND_ANYWAYS', this.module),
                onConfirm: sendEmail
            });
        } else {
            sendEmail();
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
            return !_.isEmpty($.trim(value));
        }
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
        if (model) {
            var emailTemplate = app.data.createBean('EmailTemplates', { id: model.id });
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
        app.alert.show('delete_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('LBL_EMAILTEMPLATE_MESSAGE_SHOW_MSG', this.module),
            onConfirm: _.bind(this._insertTemplate, this, template)
        });
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
            this._updateEditorWithSignature(this._lastSelectedSignature);
        }
    },

    /**
     * Hide attachment field row if no attachments, show when added
     */
    _setAttachmentVisibility: function() {
        var field = this.getField('attachments');
        var $el = field.getFieldElement();
        var $row = $el.closest('.row-fluid');
        var attachments = this.model.get('attachments');

        if (_.isEmpty(attachments)) {
            $row.addClass('hidden');
            $row.removeClass('single');
        } else {
            $row.removeClass('hidden');
            $row.addClass('single');
        }

        this.resizeEditor();
    },

    /**
     * Open the drawer with the signature selection layout. The callback should take the data passed to it and insert
     * the signature in the correct place.
     *
     * @private
     */
    launchSignatureDrawer: function() {
        app.drawer.open(
            {
                layout: 'selection-list',
                context: {
                    module: 'UserSignatures'
                }
            },
            _.bind(this._updateEditorWithSignature, this)
        );
    },

    /**
     * Fetches the signature content using its ID and updates the editor with the content.
     *
     * @param {Data.Bean} model
     */
    _updateEditorWithSignature: function(model) {
        if (model && model.id) {
            var signature = app.data.createBean('UserSignatures', { id: model.id });

            signature.fetch({
                success: _.bind(function(model) {
                    if (this.disposed === true) return; //if view is already disposed, bail out
                    if (this._insertSignature(model)) {
                        this._lastSelectedSignature = model;
                    }
                }, this),
                error: _.bind(function(error) {
                    this._showServerError(error);
                }, this)
            });
        }
    },

    /**
     * Inserts the signature into the editor.
     *
     * @param {Data.Bean} signature
     * @return {boolean}
     * @private
     */
    _insertSignature: function(signature) {
        var htmlBodyObj;

        if (_.isObject(signature) && signature.get('signature_html')) {
            var emailBody = this.model.get('description_html'),
                signatureOpenTag = '<div class="signature keep">',
                signatureCloseTag = '</div>',
                signatureContent = this._formatSignature(signature.get('signature_html'));

            // insert signature at cursor location
            emailBody = this._insertInEditor(signatureOpenTag + signatureContent + signatureCloseTag);
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

            return true;
        }

        return false;
    },

    /**
     * Inserts the content into the tinyMCE editor at the cursor location.
     *
     * @param {String} content
     * @return {String} the content of the editor
     * @private
     */
    _insertInEditor: function(content) {
        var editor = this.getField('description_html').getEditor();

        if (!_.isEmpty(content) && !_.isNull(editor)) {
            editor.execCommand(
                'mceInsertContent',
                false,
                '<div></div>' + content + '<div></div>'
            );

            return editor.getContent();
        }

        // No content for signature found or no editor instance found, return the model html_body
        return this.model.get('description_html');
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
        var $editor, headerHeight, recordHeight, showHideHeight, diffHeight, editorHeight, newEditorHeight;

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
    }
})
