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

    _signatureBtn: null,

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
                    this._handleButtonClick('upload_attachment');
                }, this)
            }, {
                text: app.lang.get('LBL_ATTACH_SUGAR_DOC', this.module),
                onclick: _.bind(function() {
                    this._handleButtonClick('sugardoc_attachment');
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
        editor.addButton('sugartemplate', {
            tooltip: app.lang.get('LBL_TEMPLATE', this.module),
            icon: 'file-o',
            onclick: _.bind(function() {
                this._handleButtonClick('template');
            }, this)
        });
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

            // ensure there is a signature to select
            if (signatures.length > 0) {
                // enable the signature button
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
