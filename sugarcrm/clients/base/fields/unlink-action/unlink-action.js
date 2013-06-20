/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    /**
     * Unlink row action used in Subpanels.
     * Triggers 'list:unlinkrow:fire' event on context on click.
     *
     * @class View.Fields.UnlinkActionField
     * @alias SUGAR.App.view.fields.UnlinkActionField
     * @extends View.Fields.RowactionField
     */
    extendsFrom: 'RowactionField',
    /**
     * @param options
     * @override
     */
    initialize: function(options) {
        options.def.event =  options.def.event || 'list:unlinkrow:fire';  // default event
        options.def.acl_action =  options.def.event || 'delete';  // default ACL
        app.view.invokeParent(this, {type: 'field', name: 'rowaction', method: 'initialize', args:[options]});
        this.type = 'rowaction';
    },
    /**
     * We cannot unlink one-to-many relationships where the relationship is a required field.
     * Returns false if relationship is required otherwise calls parent for additional ACL checks
     * @return {Boolean} true if allow access, false otherwise
     * @override
     */
    hasAccess: function() {
        var link = this.context.get("link");
        var relatedFields = app.data.getRelateFields(this.context.get("parentModule"), link);
        var requiredField = _.find(relatedFields, function(field){
            return field.required === true;
        }, this);
        if(!_.isEmpty(requiredField)){
            return false;
        }
        return app.view.invokeParent(this, {type: 'field', name: 'rowaction', method: 'hasAccess'});
    }
})
