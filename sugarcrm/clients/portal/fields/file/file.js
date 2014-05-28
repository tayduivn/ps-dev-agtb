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
 * @class View.Fields.Portal.FileField
 * @alias SUGAR.App.view.fields.PortalFileField
 * @extends View.Fields.Base.FileField
 */
({
    extendsFrom:'FileField',
    /**
     * This is overriden by portal in order to prepend site url
     * @param {String} uri
     * @returns {String} formatted uri
     */
    formatUri: function(uri) {
        return app.config.siteUrl + '/' + uri;
    }
})
