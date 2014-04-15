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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    extendsFrom: 'EditmodalView',

    /**
     * overload baseeditmodalview and remove the 'file' type fields to prevent an issue with portal users
     * being unable to upload notes
     * @param {object} model
     */
    processModel: function(model) {
        this._super('processModel', [model]);

        if (model) {
            model.set('portal_flag', true);

            // remove all fields with type 'file'
            _.each(model.fields, function(field) {
                if (field.type === 'file') {
                    model.unset(field.name);
                }
            });
        }
    }
})
