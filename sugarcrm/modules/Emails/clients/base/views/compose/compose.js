/**
 * View for the email composition layout that contains the HTML editor.
 */
({
    extendsFrom: 'RecordView',

    initialize: function(options) {
        options.context.set('create', true);
        app.view.views.RecordView.prototype.initialize.call(this, options);
        this.events = _.extend({}, this.events, {
            'click .cc-option': 'showSenderOptionField',
            'click .bcc-option': 'showSenderOptionField',
            'click [name=draft_button]': 'saveAsDraft',
            'click [name=send_button]': 'send',
            'click [name=cancel_button]': 'cancel'
        });
    },

    _render:function () {
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
    },

    bindDataChange: function() {
        // If email is considered valid, enable the dropdown menu.  If not, disable
        this.model.on('change', function() {
            if (this.isEmailSendable()) {
                this.getField('main_dropdown').setDisabled(false);
            } else {
                this.getField('main_dropdown').setDisabled(true);
            }
        }, this);
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
            ccField = field.$el.closest('.row-fluid.panel_body'),
            senderOptionTemplate = app.template.getView("compose-senderoptions", this.module);

        $(senderOptionTemplate({
            'module' : this.module,
            'showCC': showCCLink,
            'showBCC': showBCCLink,
            'showSeperator': showCCLink && showBCCLink
        })).insertAfter(ccField.find('div span.normal'));
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
        field.$el.closest('.row-fluid.panel_body').addClass('hide');
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
                module:recipientModel.get("_module")
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
                name = recipientModel.get("assigned_user_name");
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
        this.context.trigger("drawer:hide");
        if (this.context.parent)
            this.context.parent.trigger("drawer:hide");
    },

    /**
     * Format the recipient addresses in the  format the Mail API requires
     *
     * @param sendModel
     */
    hydrateSendEmailModel: function(sendModel) {
        var model = this.model;
        sendModel.set(_.extend({}, model.attributes, {
            to_addresses: [ {
                email: model.get('to_addresses')
            }],
            cc_addresses: [ {
                email: model.get('cc_addresses')
            }],
            bcc_addresses: [ {
                email: model.get('bcc_addresses')
            }]
        }));
    },

    /**
     * Build a backbone model that will be sent to the Mail API
     */
    initializeSendEmailModel: function() {
        var view = this;
        var SaveModel = Backbone.Model.extend({
            sync: function (method, model, options) {
                view.hydrateSendEmailModel(this);
                var myURL = app.api.buildURL('Mail');
                return app.api.call(method, myURL, model, options);
            }
        });
        return new SaveModel;
    },

    /**
     * Save the email as a draft for later sending
     */
    saveAsDraft: function() {
        this.saveModel('draft',
            app.lang.getAppString('LBL_EMAIL_SAVING'),
            app.lang.getAppString('LBL_EMAIL_SAVE_DRAFT_SUCCESS'));
    },

    /**
     * Send the email immediately or warn if user did not provide subject or body
     */
    send: function() {
        var sendEmail = _.bind(function() {
            this.saveModel('ready',
                app.lang.getAppString('LBL_EMAIL_SENDING'),
                app.lang.getAppString('LBL_EMAIL_SEND_SUCCESS'));
        }, this);

        if (!this.isFieldPopulated('subject')) {
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
    saveModel: function(status, pendingMessage, successMessage) {
        app.alert.show('save_edit_view', {level: 'process', title: pendingMessage});

        this.sendModel = this.initializeSendEmailModel();

        this.sendModel.set('status', status);
        this.sendModel.save(null, {
            success: function(data, textStatus, jqXHR) {
                app.alert.dismiss('save_edit_view');
                app.alert.show('save_edit_view', {autoclose: true, level: 'success', title: successMessage});
            },
            error: function(jqXHR, textStatus, errorThrown) {
                app.alert.dismiss('save_edit_view');
                var msg = {autoclose: false, level: 'error', title: app.lang.getAppString('LBL_EMAIL_SEND_FAILURE')};

                if(_.isString(textStatus.description)) {
                    msg.messages = [textStatus.description];
                }

                app.alert.show('save_edit_view', msg);
            },
            complete: function() {
                setTimeout(function() {
                    app.alert.dismiss('save_edit_view');
                }, 2000);
            },

            fieldsToValidate: this.getFields(this.module)
        });
    },

    /**
     * Can this email be sent?
     * @return {*}
     */
    isEmailSendable: function() {
        return this.isFieldPopulated('to_addresses') &&
            (this.isFieldPopulated('subject') || this.isFieldPopulated('html_body'));
    },

    /**
     * Is this field populated?
     * @param fieldName
     * @return {Boolean}
     */
    isFieldPopulated: function(fieldName) {
        return ($.trim(this.model.get(fieldName)) !== '');
    }

})
