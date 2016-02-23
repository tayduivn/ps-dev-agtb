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
     * @inheritdoc
     */
    addCustomButtons: function(editor) {
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
            tooltip: app.lang.get('LBL_SIGNATURE', this.module),
            icon: 'pencil',
            onclick: _.bind(function() {
                this._handleButtonClick('signature');
            }, this)
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
     * Notify on the context when any of these toolbar buttons are clicked.
     *
     * @param {string} buttonName
     * @private
     */
    _handleButtonClick: function(buttonName) {
        this.context.trigger('tinymce:' + buttonName + ':clicked');
    }
})
