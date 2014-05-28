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
 * @class View.Fields.Base.Emails.QuickcreateField
 * @alias SUGAR.App.view.fields.BaseEmailsQuickcreateField
 * @extends View.Fields.Base.QuickcreateField
 */
({
    extendsFrom: 'QuickcreateField',
    plugins: ['EmailClientLaunch'],

    /**
     * Used by EmailClientLaunch as a hook point to retrieve email options that are specific to a view/field
     * In this case we are using it to retrieve the parent model to make this email compose launching
     * context aware - prepopulating the to address with the given model and the parent relate field
     *
     * @returns {Object}
     * @private
     */
    _retrieveEmailOptionsFromLink: function() {
        var context = this.context.parent || this.context,
            parentModel = context.get('model'),
            emailOptions = {};

        if (parentModel && parentModel.id) {
            // set parent model as option to be passed to compose for To address & relate
            // if parentModel does not have email, it will be ignored as a To recipient
            // if parentModel's module is not an available module to relate, it will also be ignored
            emailOptions = {
                to_addresses: [{bean: parentModel}],
                related: parentModel
            };
        }

        return emailOptions;
    }
})
