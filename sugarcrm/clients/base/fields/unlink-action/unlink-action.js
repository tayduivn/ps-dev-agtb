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
        app.view.invokeParent(this, {type: 'field', name: 'rowaction', method: 'initialize', args:[options]});
        this.type = 'rowaction';
    },

    /**
     * {@inheritDoc}
     *
     * If parent module matches `Homepage` then `false` is returned.
     *
     * Plus, we cannot unlink one-to-many relationships when the relationship
     * is a required field - if that's the case `false` is returned as well.
     *
     * @return {Boolean} `true` if access is allowed, `false` otherwise.
     */
    hasAccess: function() {
        var parentModule = this.context.get('parentModule');
        if (parentModule === 'Home') {
            return false;
        }

        var link = this.context.get('link');
        if (link && app.utils.isRequiredLink(parentModule, link)) {
            return false;
        }

        return this._super('hasAccess');
    }
})
