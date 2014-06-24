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
 * @class View.Fields.Portal.KBDocuments.FileField
 * @alias SUGAR.App.view.fields.PortalKBDocumentsFileField
 * @extends View.Fields.Base.FileField
 */
({
    extendsFrom: 'FileField',
    /**
     * KB Docs needs special handling to deal with document revisions being the actual source of the file
     * @param {Mixed} value
     * @returns {Array} array of attachments
     */
    format: function (value) {
        var attachments = [];
        value = _.isArray(value) ? value : [value];
        _.each(value, function (file, idx) {
            var urlOpts = {
                    module: file.module,
                    id: file.id,
                    field: file.field_name
                },
                fileObj = this._createFileObj(file.name, urlOpts);
            attachments.push(fileObj);
        }, this);
        // Cannot be a hard check against "list" since subpanel-list needs this too
        return attachments;
    }
})
