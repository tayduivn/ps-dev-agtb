/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * Emailaction is a button that when selected will launch the appropriate email
 * client.
 *
 * @class View.Fields.Base.EmailactionField
 * @alias SUGAR.App.view.fields.BaseEmailactionField
 * @extends View.Fields.Base.ButtonField
 */
({
    extendsFrom: 'ButtonField',
    plugins: ['EmailClientLaunch'],

    initialize: function(options) {
        this._super("initialize", [options]);
        this._setEmailOptions();
    },

    _setEmailOptions: function() {
        var context = this.context.parent || this.context,
            parentModel = context.get('model');

        if (this.def.set_recipient_to_parent) {
            this.addEmailOptions({to_addresses: [{bean: parentModel}]});
        }

        if (this.def.set_related_to_parent) {
            this.addEmailOptions({related: parentModel});
        }
    }
})
