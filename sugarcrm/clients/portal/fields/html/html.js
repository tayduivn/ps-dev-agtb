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
 * @class View.Fields.Portal.HtmlField
 * @alias SUGAR.App.view.fields.PortalHtmlField
 * @extends View.Fields.Base.HtmlField
 */
({
    extendsFrom:'HtmlField',

    /**
     * This is overridden by portal in order to prepend site url to src attributes of img tag
     * @param {String} value
     * @returns {String} formatted value
     */
    format: function(value) {
        return value.replace(/(src=")(?!http:\/\/)(.*?)"/g, '$1' + app.config.siteUrl + '/$2"');
    }
})
