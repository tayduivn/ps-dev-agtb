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

    // The tinyMCE button object for the signature dropdown
    _signatureBtn: null,
    // The number of signatures found from the REST response
    _numSignatures: 0,
    // Track the editor focus/blur state
    _editorFocused: false,

    /**
     * @inheritdoc
     */
    addCustomButtons: function(editor) {
        var self = this;

        editor.addButton('sugarattachment', {
            type: 'menubutton',
            tooltip: app.lang.get('LBL_ATTACHMENT', this.module),
            icon: 'paperclip',
            menu: [{
                text: app.lang.get('LBL_ATTACH_FROM_LOCAL', this.module),
                onclick: _.bind(function() {
                    this.view.trigger('email_attachments:file:pick');
                }, this)
            }, {
                text: app.lang.get('LBL_ATTACH_SUGAR_DOC', this.module),
                onclick: _.bind(function() {
                    this.view.trigger('email_attachments:document:pick');
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
            // menu is populated from the _getSignatures() response
            menu: []
        });

        if (app.acl.hasAccess('view', 'EmailTemplates')) {
            editor.addButton('sugartemplate', {
                tooltip: app.lang.get('LBL_TEMPLATE', this.module),
                icon: 'file-o',
                onclick: _.bind(function() {
                    this._handleButtonClick('template');
                }, this)
            });
        }

        // add the events for the editor focus/blur to enable/disable the signature button
        this._addCustomEditorEvents(editor);
    },

    /**
     * Create the focus/blur events for the tinyMCE editor to interact with the signature button
     *
     * @param {Object} editor TinyMCE editor
     * @private
     */
    _addCustomEditorEvents: function(editor) {
        editor.on('focus', _.bind(function(e) {
            this._editorFocused = true;
            // the user has at least 1 signature
            if (this._numSignatures > 0) {
                // enable the signature button
                this._signatureBtn.disabled(false);
            }
        }, this));
        editor.on('blur', _.bind(function(e) {
            this._editorFocused = false;
            // the user has at least 1 signature
            if (this._numSignatures > 0) {
                // disable the signature button
                this._signatureBtn.disabled(true);
            }
        }, this));
    },

    /**
     * Return the tinyMCE editor
     *
     * @return {Mixed} editor TinyMCE editor, null if not in edit mode
     */
    getEditor: function() {
        if (!_.isEqual(this.action, 'edit')) {
            // not in edit mode, do not return the editor instance
            return null;
        }

        return this._htmleditor;
    },

    /**
     * Notify on the context when any of these toolbar buttons are clicked.
     *
     * @param {string} buttonName
     * @private
     */
    _handleButtonClick: function(buttonName) {
        var args = [].slice.call(arguments, 0);
        // Overwrite the button name argument to be the proper event name
        args[0] = 'tinymce:' + buttonName + ':clicked';

        this.context.trigger.apply(this.context, args);
    },

    /**
     * Fetches the signatures for the current user.
     *
     * @private
     */
    _getSignatures: function() {
        var signatures = app.data.createBeanCollection('UserSignatures', {
            user_id: app.user.get('id')
        });

        signatures.fetch({
            success: _.bind(this._getSignaturesSuccess, this),
            error: _.bind(this._getSignaturesError, this)
        });
    },

    /**
     * Successfully fetched the signatures for the current user.
     *
     * @param {Data.BeanCollection} signatures
     * @private
     */
    _getSignaturesSuccess: function(signatures) {
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
                    onclick: _.bind(function() {
                        this._handleButtonClick('selected_signature', signature);
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
     * Failed to fetch the signatures for the current user.
     *
     * @param {SUGAR.HttpError} error
     * @private
     */
    _getSignaturesError: function(error) {
        app.alert.show('server-error', {
            level: 'error',
            messages: 'ERR_GENERIC_SERVER_ERROR'
        });
    }
})