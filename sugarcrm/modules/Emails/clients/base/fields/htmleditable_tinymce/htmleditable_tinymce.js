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
 * @class View.Fields.Base.Emails.Htmleditable_tinymceField
 * @alias SUGAR.App.view.fields.BaseEmailsHtmleditable_tinymceField
 * @extends View.Fields.Base.Htmleditable_tinymceField
 */
({
    extendsFrom: 'Htmleditable_tinymceField',

    /**
     * Force the field to display the correct view even if there is no data to
     * show.
     *
     * @property {boolean}
     */
    showNoData: false,

    /**
     * Constant for inserting content above the existing email body.
     *
     * @property {string}
     */
    ABOVE_CONTENT: 'above',

    /**
     * Constant for inserting content below the existing email body.
     *
     * @property {string}
     */
    BELOW_CONTENT: 'below',

    /**
     * Constant for inserting content into the email body at the current cursor
     * location.
     *
     * @property {string}
     */
    CURSOR_LOCATION: 'cursor',

    /**
     * The tinyMCE button object for the signature dropdown.
     *
     * @private
     * @property {Object|null}
     */
    _signatureBtn: null,

    /**
     * The number of signatures found from the API response.
     *
     * @private
     * @property {number}
     */
    _numSignatures: 0,

    /**
     * Track the editor focus/blur state.
     *
     * @private
     * @property {boolean}
     */
    _editorFocused: false,

    /**
     * @inheritdoc
     *
     * Stores the user's default signature on the context using the attribute
     * name `current_signature`. This attribute is updated anytime a new
     * signature is selected.
     *
     * Stores the initial signature location for inserting the default
     * signature. If the context already has `signature_location` attribute,
     * then that value is used. Otherwise, this attribute is defaulted to
     * insert the signature below any content. This attribute is updated
     * anytime a signature is inserted in a different location.
     *
     * The default signature is inserted in the initial location if the email
     * is new. The signature is not inserted if the email is an existing draft
     * that is being edited. If the initial location is the cursor, then the
     * signature is inserted after the editor is fully loaded and the cursor
     * has been placed.
     *
     * For new replies, the cursor is placed above the reply content, once the
     * editor has been loaded.
     */
    initialize: function(options) {
        var signature;
        var location;

        this._super('initialize', [options]);

        // Get the default signature and store it on the context.
        signature = app.user.getPreference('signature_default');

        if (!(signature instanceof app.Bean)) {
            signature = app.data.createBean('UserSignatures', signature);
        }

        this.context.set('current_signature', signature);

        // Determine the initial signature location for inserting the default.
        location = this.context.get('signature_location');

        if (_.isEmpty(location)) {
            // Default the location.
            location = this.BELOW_CONTENT;
            this.context.set('signature_location', location);
        }

        // Don't do the following if updating an existing draft.
        if (this.model.isNew()) {
            // Insert the default signature.
            if (location === this.CURSOR_LOCATION) {
                // Need to wait for the editor before inserting.
                this.listenToOnce(this.context, 'tinymce:oninit', function() {
                    this._insertSignature(signature, location);
                });
            } else {
                this._insertSignature(signature, location);
            }

            // Focus editor at top of the content for replies.
            if (!_.isEmpty(this.model.get('reply_to_id'))) {
                this.listenToOnce(this.context, 'tinymce:oninit', function() {
                    if (this._htmleditor) {
                        $(this._htmleditor).focus();
                        this._htmleditor.selection.select(this._htmleditor.getBody(), true);
                        this._htmleditor.selection.collapse(true);
                    }
                });
            }
        }
    },

    /**
     * @inheritdoc
     *
     * Resize the field's container based on the height of the iframe content
     * for preview.
     */
    setViewContent: function(value) {
        var field;
        // Pad this to the final height due to the iframe margins/padding
        var padding = 25;
        var contentHeight = 0;

        this._super('setViewContent', [value]);

        // Only set this field height if it is in the preview pane
        if (this.tplName !== 'preview') {
            return;
        }

        contentHeight = this._getContentHeight() + padding;

        // Only resize the editor when the content is fully loaded
        if (contentHeight > padding) {
            // Set the maximum height to 400px
            if (contentHeight > 400) {
                contentHeight = 400;
            }

            field = this._getHtmlEditableField();
            field.css('height', contentHeight);
        }
    },

    /**
     * Get the content height of the field's iframe.
     *
     * @private
     * @return {number} Returns 0 if the iframe isn't found.
     */
    _getContentHeight: function() {
        var field = this._getHtmlEditableField();

        if (!_.isUndefined(field.get(0)) && !_.isEmpty(field.get(0).contentDocument)) {
            return field.contents().find('body')[0].offsetHeight;
        }

        return 0;
    },

    /**
     * @inheritdoc
     *
     * Adds buttons for uploading a local file and selecting a Sugar Document
     * to attach to the email.
     *
     * Adds a button for selecting and inserting a signature at the cursor.
     *
     * Adds a button for selecting and applying a template.
     *
     * @fires email_attachments:file on the view when the user elects to attach
     * a local file.
     */
    addCustomButtons: function(editor) {
        var self = this;

        editor.addButton('sugarattachment', {
            type: 'menubutton',
            tooltip: app.lang.get('LBL_ATTACHMENT', this.module),
            icon: 'paperclip',
            onclick: function(event) {
                // Track click on the attachment button.
                app.analytics.trackEvent('click', 'tinymce_email_attachment_button', event);
            },
            menu: [{
                text: app.lang.get('LBL_ATTACH_FROM_LOCAL', this.module),
                onclick: _.bind(function(event) {
                    // Track click on the file attachment button.
                    app.analytics.trackEvent('click', 'tinymce_email_attachment_file_button', event);
                    this.view.trigger('email_attachments:file');
                }, this)
            }, {
                text: app.lang.get('LBL_ATTACH_SUGAR_DOC', this.module),
                onclick: _.bind(function(event) {
                    // Track click on the document attachment button.
                    app.analytics.trackEvent('click', 'tinymce_email_attachment_doc_button', event);
                    this._selectDocument();
                }, this)
            }]
        });

        editor.addButton('sugarsignature', {
            type: 'menubutton',
            tooltip: app.lang.get('LBL_SIGNATURE', this.module),
            icon: 'pencil',
            // disable the signature button until they have been loaded
            disabled: true,
            onPostRender: function() {
                self._signatureBtn = this;
                // load the users signatures
                self._getSignatures();
            },
            onclick: function(event) {
                // Track click on the signature button.
                app.analytics.trackEvent('click', 'tinymce_email_signature_button', event);
            },
            // menu is populated from the _getSignatures() response
            menu: []
        });

        if (app.acl.hasAccess('view', 'EmailTemplates')) {
            editor.addButton('sugartemplate', {
                tooltip: app.lang.get('LBL_TEMPLATE', this.module),
                icon: 'file-o',
                onclick: _.bind(function(event) {
                    // Track click on the template button.
                    app.analytics.trackEvent('click', 'tinymce_email_template_button', event);
                    this._selectEmailTemplate();
                }, this)
            });
        }

        // Enable the signature button when the editor is focused and the user
        // has signatures that can be inserted.
        editor.on('focus', _.bind(function(e) {
            this._editorFocused = true;
            this.view.trigger('tinymce:focus');
            // the user has at least 1 signature
            if (this._numSignatures > 0) {
                // enable the signature button
                this._signatureBtn.disabled(false);
            }
        }, this));

        // Disable the signature button when the editor is blurred and the user
        // has signatures. Signatures are inserted at the cursor location. If
        // the button is not disabled when the editor is unfocused, then issues
        // would arise with the user clicking a signature to insert at the
        // cursor without a cursor being present.
        editor.on('blur', _.bind(function(e) {
            this._editorFocused = false;
            this.view.trigger('tinymce:blur');
            // the user has at least 1 signature
            if (this._numSignatures > 0) {
                // disable the signature button
                this._signatureBtn.disabled(true);
            }
        }, this));
    },

    /**
     * Inserts the content into the TinyMCE editor at the specified location.
     *
     * @private
     * @param {string} content
     * @param {string} [location="cursor"] Whether to insert the new content
     *   above existing content, below existing content, or at the cursor
     *   location. Defaults to being inserted at the cursor position.
     * @return {string} The updated content.
     */
    _insertInEditor: function(content, location) {
        var emailBody = this.model.get(this.name) || '';

        if (_.isEmpty(content)) {
            return emailBody;
        }

        // Default to the cursor location.
        location = location || this.CURSOR_LOCATION;

        // Add empty divs so user can place the cursor on the line before or
        // after.
        content = '<div></div>' + content + '<div></div>';

        if (location === this.CURSOR_LOCATION) {
            if (_.isNull(this._htmleditor)) {
                // Unable to insert content at the cursor without an editor.
                return emailBody;
            }

            this._htmleditor.execCommand('mceInsertContent', false, content);

            // Get the HTML content from the editor.
            emailBody = this._htmleditor.getContent();
        } else if (location === this.BELOW_CONTENT) {
            emailBody += content;
        } else if (location === this.ABOVE_CONTENT) {
            emailBody = content + emailBody;
        }

        // Update the model with the new content.
        this.model.set(this.name, emailBody);

        return emailBody;
    },

    /**
     * Fetches the signatures for the current user.
     *
     * @private
     */
    _getSignatures: function() {
        var signatures = app.data.createBeanCollection('UserSignatures');

        signatures.filterDef = [{
            user_id: {$equals: app.user.get('id')}
        }];
        signatures.fetch({
            max_num: -1, // Get as many as we can.
            success: _.bind(this._getSignaturesSuccess, this),
            error: function() {
                app.alert.show('server-error', {
                    level: 'error',
                    messages: 'ERR_GENERIC_SERVER_ERROR'
                });
            }
        });
    },

    /**
     * Add each signature as buttons under the signature button.
     *
     * @private
     * @param {Data.BeanCollection} signatures
     */
    _getSignaturesSuccess: function(signatures) {
        if (this.disposed === true) {
            return;
        }

        if (!_.isUndefined(signatures) && !_.isUndefined(signatures.models)) {
            signatures = signatures.models;
        } else {
            app.alert.show('server-error', {
                level: 'error',
                messages: 'ERR_GENERIC_SERVER_ERROR'
            });

            return;
        }

        if (!_.isNull(this._signatureBtn)) {
            // write the signature names to the control dropdown
            _.each(signatures, _.bind(function(signature) {
                this._signatureBtn.settings.menu.push({
                    text: signature.get('name'),
                    onclick: _.bind(function(event) {
                        // Track click on a signature.
                        app.analytics.trackEvent('click', 'email_signature', event);
                        this._insertSignature(signature, this.CURSOR_LOCATION);
                    }, this)
                });
            }, this));

            // Set the number of signatures the user has
            this._numSignatures = signatures.length;

            // If the editor is focused before the signatures are returned, enable the signature button
            if (this._editorFocused) {
                this._signatureBtn.disabled(false);
            }
        }
    },

    /**
     * Inserts the signature into the editor.
     *
     * @private
     * @param {Data.Bean} signature
     * @param {string} [location="cursor"] Whether to insert the new content
     * above existing content, below existing content, or at the cursor
     * location. Defaults to being inserted at the cursor position.
     */
    _insertSignature: function(signature, location) {
        var htmlBodyObj;
        var emailBody;
        var signatureHtml;
        var decodedSignature;
        var signatureContent;

        function decodeBrackets(str) {
            str = str.replace(/&lt;/gi, '<');
            str = str.replace(/&gt;/gi, '>');

            return str;
        }

        if (this.disposed === true) {
            return;
        }

        if (!(signature instanceof app.Bean)) {
            return;
        }

        signatureHtml = signature.get('signature_html');

        if (_.isEmpty(signatureHtml)) {
            return;
        }

        decodedSignature = decodeBrackets(signatureHtml);
        signatureContent = '<div class="signature keep">' + decodedSignature + '</div>';

        emailBody = this._insertInEditor(signatureContent, location);
        htmlBodyObj = $('<div>' + emailBody + '</div>');

        // Mark each signature to either keep or remove.
        $('div.signature', htmlBodyObj).each(function() {
            if (!$(this).hasClass('keep')) {
                // Mark for removal.
                $(this).addClass('remove');
            } else {
                // If the parent is also a signature, move the node out of the
                // parent so it isn't removed.
                if ($(this).parent().hasClass('signature')) {
                    // Move the signature outside of the nested signature.
                    $(this).parent().before(this);
                }

                // Remove the "keep" class so if another signature is added it
                // will remove this one.
                $(this).removeClass('keep');
            }
        });

        // After each signature is marked, perform the removal.
        htmlBodyObj.find('div.signature.remove').remove();

        emailBody = htmlBodyObj.html();
        this.model.set(this.name, emailBody);

        this.context.set('current_signature', signature);
        this.context.set('signature_location', location || this.CURSOR_LOCATION);
    },

    /**
     * Allows the user to select a template to apply.
     *
     * @private
     */
    _selectEmailTemplate: function() {
        var def = {
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
        };

        app.drawer.open(def, _.bind(this._onEmailTemplateDrawerClose, this));
    },

    /**
     * Verifies that the user has access to the email template before applying
     * it.
     *
     * @private
     * @param {Data.Bean} model
     */
    _onEmailTemplateDrawerClose: function(model) {
        var emailTemplate;

        if (this.disposed === true) {
            return;
        }

        // This is an edge case where user has List but not View permission.
        // Search & Select will return only id and name if View permission is
        // not permitted for this record. Display appropriate error.
        if (model && _.isUndefined(model.subject)) {
            app.alert.show('no_access_error', {
                level: 'error',
                messages: app.lang.get('ERR_NO_ACCESS', this.module, {name: model.value})
            });
        } else if (model) {
            // `value` is not a real attribute.
            emailTemplate = app.data.createBean('EmailTemplates', _.omit(model, 'value'));
            this._confirmTemplate(emailTemplate);
        }
    },

    /**
     * Confirms that the user wishes to replace all content in the editor. The
     * template is applied if there is no existing content or if the user
     * confirms "yes".
     *
     * @private
     * @param {Data.Bean} template
     */
    _confirmTemplate: function(template) {
        var subject = this.model.get('name') || '';
        var text = this.model.get('description') || '';
        var html = this.model.get(this.name) || '';
        var fullContent = subject + text + html;

        if (_.isEmpty(fullContent)) {
            this._applyTemplate(template);
        } else {
            app.alert.show('delete_confirmation', {
                level: 'confirmation',
                messages: app.lang.get('LBL_EMAILTEMPLATE_MESSAGE_SHOW_MSG', this.module),
                onConfirm: _.bind(function(event) {
                    // Track click on confirmation button.
                    app.analytics.trackEvent('click', 'email_template_confirm', event);
                    this._applyTemplate(template);
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
     * The template's subject does not overwrite the existing subject if:
     *
     * 1. The email is a reply.
     * 2. The template does not have a subject.
     *
     * @private
     * @fires email_attachments:template on the view with the selected template
     * as a parameter. {@link View.Fields.Base.Emails.EmailAttachmentsField}
     * adds the template's attachments to the email.
     * @param {Data.Bean} template
     */
    _applyTemplate: function(template) {
        var body;
        var replyContent;
        var subject;
        var signature = this.context.get('current_signature');

        /**
         * Check the email body and pull out any reply content from a draft
         * email.
         *
         * @param {string} body The full content to search.
         * @return {string} The reply content.
         */
        function getReplyContent(body) {
            var reply = '';
            var $reply;

            if (body) {
                $reply = $('<div>' + body + '</div>').find('div#replycontent');

                if ($reply.length > 0) {
                    reply = $reply[0].outerHTML;
                }
            }

            return reply;
        }

        if (this.disposed === true) {
            return;
        }

        // Track applying an email template.
        app.analytics.trackEvent('email_template', 'apply', template);

        replyContent = getReplyContent(this.model.get(this.name));
        subject = template.get('subject');

        // Only use the subject if it's not a reply.
        if (subject && !replyContent) {
            this.model.set('name', subject);
        }

        //TODO: May need to move over replaces special characters.
        body = template.get('text_only') ? template.get('body') : template.get('body_html');
        this.model.set(this.name, body);

        this.view.trigger('email_attachments:template', template);

        // The HTML signature is used even when the template is text-only.
        if (signature) {
            this._insertSignature(signature, this.BELOW_CONTENT);
        }

        // Append the reply content to the end of the email.
        if (replyContent) {
            this._insertInEditor(replyContent, this.BELOW_CONTENT);
        }
    },

    /**
     * Allows the user to select a document to attach.
     *
     * @private
     * @fires email_attachments:document on the view with the selected document
     * as a parameter. {@link View.Fields.Base.EmailAttachmentsField} attaches
     * the document to the email.
     */
    _selectDocument: function() {
        var def = {
            layout: 'selection-list',
            context: {
                module: 'Documents'
            }
        };

        app.drawer.open(def, _.bind(function(model) {
            var document;

            if (model) {
                // `value` is not a real attribute.
                document = app.data.createBean('Documents', _.omit(model, 'value'));
                this.view.trigger('email_attachments:document', document);
            }
        }, this));
    }
})
