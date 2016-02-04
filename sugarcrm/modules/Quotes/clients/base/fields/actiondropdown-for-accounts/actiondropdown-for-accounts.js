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

 * @class View.Fields.Quotes.ActiondropdownForAccountsField
 * @alias SUGAR.App.view.fields.QuotesActiondropdownForAccountsField
 * @extends View.Fields.Base.ActiondropdownField
 */
({
    extendsFrom: 'ActiondropdownField',

    /**
     * @inheritdoc
     */
    _render: function(){
        this._super('_render');
        //force the action dropdown to be disabled so we don't show the link-existing button. Removing the button
        //breaks rendering of the UI control (no dropdown toggle appears).
        this.$(this.actionDropDownTag).toggleClass('disabled', true);

        return this;
    }
});
