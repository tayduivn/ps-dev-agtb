/**
 * View for the email composition layout that contains the HTML editor.
 */
({
    extendsFrom: 'RecordView',

    initialize: function(options) {
        _.bindAll(this);
        app.view.views.RecordView.prototype.initialize.call(this, options);
        this.events = _.extend({}, this.events, {
            'click .cc-option': 'showSenderOptionField',
            'click .bcc-option': 'showSenderOptionField',
            'click [name=draft_button]': 'saveAsDraft',
            'click [name=send_button]': 'send',
            'click [name=cancel_button]': 'cancel'
        });
        this.context.on('actionbar:template_button:clicked', this.launchTemplateDrawer);
        this.context.on('actionbar:attach_sugardoc_button:clicked', this.launchDocumentDrawer);
        this.context.on("actionbar:signature_button:clicked", this._launchSignatureDrawer);
        this.context.on('attachments:updated', this.toggleAttachmentVisibility);
    },

    _render: function () {
        app.view.views.RecordView.prototype._render.call(this);

        if (this.createMode) {
            this.setTitle(app.lang.get('LBL_COMPOSEEMAIL', this.module));
        }

        if (this.model.isNotEmpty) {
            this.renderSenderOptions();

            // initialize the TO recipients field with data from the recipientModel, if the user clicked on an email address somewhere in the application
            // and was routed to the quick compose view
            var recipientModel = this.context.get("recipientModel");

            if (!_.isEmpty(recipientModel)) {
                this.populateToRecipients(recipientModel);
            }
        }

        this.initMainButtonStatus();
    },

    /**
     * Set enabled/disabled status on the page action dropdown menu based on whether email is sendable
     * And listen for changes to the relevant field to enable the action dropdown when it becomes sendable
     */
    initMainButtonStatus: function() {
        //If email is considered valid, enable the dropdown menu.  If not, disable
        var toggleMainButtons = _.bind(function() {
            this.setMainButtonsDisabled(!(this.isEmailSendable()));
        }, this);

        //Call toggle immediately to initialize the buttons appropriately
        toggleMainButtons();

        //Then set up listeners
        this.getField('to_addresses').getFieldElement().keyup(toggleMainButtons);
    },

    /**
     * Enable/disable the page action dropdown menu based on whether email is sendable
     * @param enabled
     */
    setMainButtonsDisabled: function(disabled) {
        this.getField('main_dropdown').setDisabled(disabled);
    },

    /**
     * Check if CC or BCC fields have values - if not, hide the fields and inject a link to show it
     */
    renderSenderOptions: function() {
        var showCCLink = false,
            showBCCLink = false,
            toCC = this.model.get('cc_addresses'),
            toBCC = this.model.get('bcc_addresses');

        if (this.model.isNew() || _.isEmpty(toCC)) {
            this.hideField('cc_addresses');
            showCCLink = true;
        }

        if (this.model.isNew() || _.isEmpty(toBCC)) {
            this.hideField('bcc_addresses');
            showBCCLink = true;
        }

        this.toggleSenderOptions('to_addresses', showCCLink, showBCCLink);
    },

    /**
     * Run the sender option template to toggle whether CC or BCC show links are injected
     *
     * @param container
     * @param showCCLink
     * @param showBCCLink
     */
    toggleSenderOptions: function(container, showCCLink, showBCCLink) {
        var field = this.getField(container),
            ccField,
            senderOptionTemplate;

        if (field) {
            ccField = field.$el.closest('.row-fluid.panel_body');
            senderOptionTemplate = app.template.getView("compose-senderoptions", this.module);

            $(senderOptionTemplate({
                'module' : this.module,
                'showCC': showCCLink,
                'showBCC': showBCCLink,
                'showSeperator': showCCLink && showBCCLink
            })).insertAfter(ccField.find('div span.normal'));
        }
    },

    /**
     * Event Handler for showing the CC or BCC options on the page.
     *
     * @param evt click event
     */
    showSenderOptionField: function(evt) {
        var ccOption = evt.target,
            fieldName = ccOption.dataset.ccfield,
            field = this.getField(fieldName),
            ccSeperator = this.$('.compose-sender-options .cc-seperator');

        this.$(ccOption).addClass('hide');
        ccSeperator.toggleClass('hide', true);

        field.$el.closest('.row-fluid.panel_body').removeClass('hide');

        //check to see if both fields are hidden then hide the whole thing
        if(this.$('.cc-option').hasClass('hide') && this.$('.bcc-option').hasClass('hide')){
            this.$('.compose-sender-options').addClass('hide');
        }
    },

    /**
     * Hides a field section on the form
     *
     * @param fieldName name of the field to hide
     */
    hideField: function(fieldName) {
        var field = this.getField(fieldName);
        if (field) {
            field.$el.closest('.row-fluid.panel_body').addClass('hide');
        }
    },

    /**
     * Initialize the TO recipients field with data from the recipientModel
     *
     * @param recipientModel
     */
    populateToRecipients: function(recipientModel) {
        // construct a new model from the data in recipientModel, which meets the expectations of the recipient field, to pass to "to_addresses"
        var recipient = new Backbone.Model({
                id:recipientModel.get("id"),
                module:recipientModel.module
            }),
            email = recipientModel.get("email"),
            email1 = recipientModel.get("email1"),
            name;

        if (!_.isEmpty(email1)) {
            // get the recipient data from the email1 and name properties
            recipient.set("email", email1);
            name = recipientModel.get("name");
        } else if (!_.isEmpty(email) && _.isArray(email)) {
            // get recipient data from the email and assigned_user_name properties
            var primaryAddress = _.find(email, function (emailAddress) {
                return (emailAddress.primary_address == "1");
            });

            if (!_.isUndefined(primaryAddress) && !_.isUndefined(primaryAddress.email_address) && primaryAddress.email_address.length > 0) {
                recipient.set("email", primaryAddress.email_address);
                name = recipientModel.get("name");
            }
        }

        if (!_.isEmpty(name)) {
            // only set the name if it's actually available
            recipient.set("name", name);
        }

        if (!_.isEmpty(recipient.get("email"))) {
            // don't bother adding the recipient unless the email address is present
            this.context.trigger("recipients:to_addresses:add", recipient);
        }
    },

    /**
     * Cancel and close the drawer
     */
    cancel: function() {
        app.drawer.close();
    },

    /**
     * Grab an array of attachments with the given type
     *
     * @param type
     * @returns array of attachments or empty array if none found
     */
    getAttachmentsByType: function(type) {
        var attachments = this.model.get('attachments') || [];

        if (!_.isArray(attachments)) {
            attachments = [attachments];
        }

        attachments = _.filter(attachments, function(attachment) {
            return (attachment.type && attachment.type == type);
        });

        return attachments;
    },

    /**
     * Build a backbone model that will be sent to the Mail API
     */
    initializeSendEmailModel: function() {
        var sendModel = new Backbone.Model(_.extend({}, this.model.attributes, {
            to_addresses: [ {
                                email: this.model.get('to_addresses')
                            }],
            cc_addresses: [ {
                                email: this.model.get('cc_addresses')
                            }],
            bcc_addresses: [ {
                                 email: this.model.get('bcc_addresses')
                             }],
            attachments: this.getAttachmentsByType('upload'),
            documents: this.getAttachmentsByType('document')
        }));
        return sendModel;
    },

    /**
     * Save the email as a draft for later sending
     */
    saveAsDraft: function() {
        this.saveModel(
            'draft',
            app.lang.get('LBL_DRAFT_SAVING', this.module),
            app.lang.get('LBL_DRAFT_SAVED', this.module),
            app.lang.get('LBL_ERROR_SAVING_DRAFT', this.module)
        );
    },

    /**
     * Send the email immediately or warn if user did not provide subject or body
     */
    send: function() {
        var sendEmail = _.bind(function() {
            this.saveModel(
                'ready',
                app.lang.get('LBL_EMAIL_SENDING', this.module),
                app.lang.get('LBL_EMAIL_SENT', this.module),
                app.lang.get('LBL_ERROR_SENDING_EMAIL', this.module)
            );
        }, this);

        if (!this.isFieldPopulated('subject') && !this.isFieldPopulated('html_body')) {
            app.alert.show('send_confirmation', {
                level: 'confirmation',
                messages: app.lang.get('LBL_NO_SUBJECT_NO_BODY_SEND_ANYWAYS', this.module),
                onConfirm: sendEmail
            });
        } else if (!this.isFieldPopulated('subject')) {
            app.alert.show('send_confirmation', {
                level: 'confirmation',
                messages: app.lang.get('LBL_SEND_ANYWAYS', this.module),
                onConfirm: sendEmail
            });
        } else if (!this.isFieldPopulated('html_body')) {
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
     * Build the backbone model to be sent to the Mail API with the appropriate status
     * Also display the appropriate alerts to give user indication of what is happening.
     *
     * @param status (draft or ready)
     * @param pendingMessage message to display while Mail API is being called
     * @param successMessage message to display when a successful Mail API response has been received
     */
    saveModel: function(status, pendingMessage, successMessage, errorMessage) {
        var myURL,
            sendModel = this.initializeSendEmailModel();

        this.setMainButtonsDisabled(true);
        app.alert.show('mail_call_status', {level: 'process', title: pendingMessage});

        sendModel.set('status', status);
        myURL = app.api.buildURL('Mail');
        response = app.api.call('create', myURL, sendModel, {
            success: function() {
                app.alert.dismiss('mail_call_status');
                app.alert.show('mail_call_status', {autoClose: true, level: 'success', title: successMessage});
            },
            error: function(error) {
                var msg = {autoClose: false, level: 'error', title: errorMessage};
                if(error && _.isString(error.message)) {
                    msg.messages = [error.message];
                }
                app.alert.dismiss('mail_call_status');
                app.alert.show('mail_call_status', msg);
            },
            complete:_.bind(function() {
                this.setMainButtonsDisabled(false);
            }, this)
        });
    },

    /**
     * Can this email be sent?
     * @return {*}
     */
    isEmailSendable: function() {
        return this.isFieldPopulated('to_addresses');
    },

    /**
     * Is this field populated?
     * @param fieldName
     * @return {Boolean}
     */
    isFieldPopulated: function(fieldName) {
        return ($.trim(this.model.get(fieldName)) !== '');
    },

    /**
     * Open the drawer with the EmailTemplates selection list layout. The callback should take the data passed to it
     * and replace the existing editor contents with the selected template.
     */
    launchTemplateDrawer: function() {
        app.drawer.open({
                layout:'compose-templates',
                context:{
                    module:'EmailTemplates',
                    forceNew:true
                }
            },
            this.templateDrawerCallback
        );
    },

    /**
     * Receives the selected template to insert and begins the process of confirming the operation and inserting the
     * template into the editor.
     *
     * @param model
     */
    templateDrawerCallback: function(model) {
        if (model) {
            var emailTemplate = app.data.createBean('EmailTemplates', { id: model.id });
            emailTemplate.fetch({
                success: _.bind(this.confirmTemplate, this),
                error: function() {
                    app.logger.error.log("error");
                }
            });
        }
    },

    /**
     * Presents the user with a confirmation prompt indicating that inserting the template will replace all content
     * in the editor. If the user confirms "yes" then the template will inserted.
     *
     * @param template
     */
    confirmTemplate: function(template) {
        app.alert.show('delete_confirmation', {
            level:'confirmation',
            messages:app.lang.get('LBL_EMAILTEMPLATE_MESSAGE_SHOW_MSG', this.module),
            onConfirm:_.bind(function() {
                this.insertTemplate(template);
            }, this)
        });
    },

    /**
     * Inserts the template into the editor.
     *
     * @param template
     */
    insertTemplate: function(template) {
        var editor,
            subject,
            notes;

        if (_.isObject(template)) {
            subject = template.get('subject');

            if (subject) {
                this.model.set('subject', subject);
            }

            editor = this.getField('html_body');

            //TODO: May need to move over replaces special characters.
            if (template.get('text_only') === 1) {
                editor.setEditorContent(template.get('body'));
            } else {
                editor.setEditorContent(template.get('body_html'));
            }

            notes = app.data.createBeanCollection("Notes");

            notes.fetch({
                'filter':{
                    "filter":[
                        {"parent_id":{"$equals":template.id}}
                    ]
                },
                success:_.bind(function(data) {
                    if (!_.isEmpty(data.models)) {
                        this.insertTemplateAttachments(data.models);
                    }
                }, this),
                error:function() {
                    app.logger.error("Unable to fetch the bean collection.");
                }
            });
        }
    },
    
    /**
     * Inserts attachments associated with the template by triggering an "add" event for each attachment to add to the
     * attachments field.
     *
     * @param attachments
     */
    insertTemplateAttachments: function(attachments) {
        this.context.trigger("attachments:remove-by-tag", 'template');
        _.each(attachments, function(attachment) {
            this.context.trigger("attachment:add", {
                id: attachment.id,
                name: attachment.filename,
                nameForDisplay: attachment.filename,
                tag: 'template',
                type: 'documents'
            });
        }, this);
    },

    /**
     * Open the drawer with the SugarDocuments attachment selection list layout. The callback should take the data
     * passed to it and add the document as an attachment.
     */
    launchDocumentDrawer: function() {
        app.drawer.open({
                layout: 'selection-list',
                context: {
                    module: 'Documents',
                    forceNew:true
                }
            },
            this.documentDrawerCallback);
    },

    /**
     * Fetches the selected SugarDocument using its ID and triggers an "add" event to add the attachment to the
     * attachments field.
     *
     * @param model
     */
    documentDrawerCallback: function(model) {
        if (model) {
            var sugarDocument = app.data.createBean('Documents', { id: model.id });
            sugarDocument.fetch({
                success:_.bind(function (model) {
                    this.context.trigger("attachment:add", {
                        id:model.id,
                        name:model.filename,
                        nameForDisplay:model.filename,
                        type: 'documents'
                    });
                }, this),
                error: function() {
                    app.logger.error("Unable to fetch the bean collection:");
                }
            });
        }
    },

    /**
     * Hide attachment field row if no attachments, show when added
     *
     * @param attachments
     */
    toggleAttachmentVisibility: function(attachments) {
        var $row = this.$('.attachments').closest('.row-fluid');
        if (attachments.length > 0) {
            $row.removeClass('hidden');
        } else {
            $row.addClass('hidden');
        }
    },

    /**
     * Open the drawer with the signature selection layout. The callback should take the data passed to it and insert
     * the signature in the correct place.
     *
     * @private
     */
    _launchSignatureDrawer: function() {
        app.drawer.open(
            {
                layout: "compose-signatures-selection",
                context: {module: this.module}
            },
            this._updateEditorWithSignature
        );
    },

    /**
     * Fetches the signature content using its ID and updates the editor with the content.
     *
     * @param signature
     */
    _updateEditorWithSignature: function(signature) {
        if (_.isObject(signature) && signature.id) {
            var url = app.api.buildURL("Signatures", signature.id);
            app.api.call("read", url, null, {
                success: this._insertSignature,
                error: function() {
                    console.log("Retrieving Signature failed.");
                }
            });
        }
    },

    /**
     * Inserts the signature into the editor.
     *
     * @param signature
     * @private
     */
    _insertSignature: function(signature) {
        var editor,
            emailBody;

        if (_.isObject(signature) && signature.signature_html) {
            editor    = this.getField("html_body");
            emailBody = editor.getEditorContent();

            emailBody += this._formatSignature(signature.signature_html);
            editor.setEditorContent(emailBody);
        }
    },

    /**
     * Formats HTML signatures to replace select HTML-entities with their true characters.
     *
     * @param signature
     */
    _formatSignature: function(signature) {
        signature = signature.replace(/&lt;/gi, "<");
        signature = signature.replace(/&gt;/gi, ">");

        return signature;
    }
})
