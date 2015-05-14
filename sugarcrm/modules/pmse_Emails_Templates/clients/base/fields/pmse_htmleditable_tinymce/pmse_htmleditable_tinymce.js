/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

({
    extendsFrom: 'Htmleditable_tinymceField',

    /**
     * @inheritdoc
     */
    addCustomButtons: function(editor) {
        editor.addButton('mybutton', {
            title : 'Fields Selector',
            image : 'modules/pmse_Project/img/icon_processmaker_32.gif',
            onclick : _.bind(this._showVariablesBook, this)
        });
    },

    /**
     * When in edit mode, the field includes an icon button for opening an address book. Clicking the button will
     * trigger an event to open the address book, which calls this method to do the dirty work. The selected recipients
     * are added to this field upon closing the address book.
     *
     * @private
     */
    _showVariablesBook: function() {
        /**
         * Callback to add recipients, from a closing drawer, to the target Recipients field.
         * @param {undefined|Backbone.Collection} recipients
         */
        var addVariables = _.bind(function(variables) {
            if (variables && variables.length > 0) {
                this.model.set(this.name, this.buildVariablesString(variables));
            }

        }, this);
        app.drawer.open(
            {
                layout:  "compose-varbook",
                context: {
                    module: "pmse_Emails_Templates",
                    mixed:  true
                }
            },
            function(variables) {
                addVariables(variables);
            }
        );
    },
    buildVariablesString: function(recipients) {
        var result = '' , newExpression = '', currentValue, i, aux, aux2;
        _.each(recipients.models, function(model) {
            newExpression += '{::'+ model.attributes.rhs_module+'::'+model.attributes.id+'::}'
        });
        //new
        var bm = this._htmleditor.selection.getBookmark();
        this._htmleditor.selection.moveToBookmark(bm);
        this._htmleditor.selection.setContent(newExpression);

//        currentValue = this._htmleditor.getWin().getSelection().extentNode.nodeValue;
//        currentValue = this._htmleditor.getContent();
////        i = this._htmleditor.getWin().getSelection().anchorOffset;
//        i = this._htmleditor.getContent().selectionStart;//.anchorOffset;
//        if (currentValue) {
//            result = currentValue.substr(0, i) + newExpression + currentValue.substr(this._htmleditor.getContent().selectionEnd, this._htmleditor.getContent().length);
//        } else {
//            result = newExpression;
//        }
        return currentValue = this._htmleditor.getContent();
    }

})
