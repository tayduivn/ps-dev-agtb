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
    _render: function() {
        var action = "view";
        if (this.def.link && this.def.route) {
            action = this.def.route.action;
        }
        if (!app.acl.hasAccessToModel(action, this.model)) {
            this.def.link = false;
        }
        if (this.def.link) {
            this.href = this.buildHref();
        }
        app.view.Field.prototype._render.call(this);
    },
    // Takes care of building href for when there's a def.link and also if is bwc enabled
    buildHref: function() {
        var module, moduleMeta, href = '', defRoute;
        module = this.context.get('module') || this.model.get("_module");
        moduleMeta = app.metadata.getModule(module) || {};
        defRoute = this.def.route ? this.def.route : {};

        // Starts as sidecar href unless we encounter bwc enabled
        href = '#' + app.router.buildRoute(module, this.model.id, defRoute.action, defRoute.options);
        if ((moduleMeta && moduleMeta.isBwcEnabled && _.isUndefined(this.def.bwcLink) || (!_.isUndefined(this.def.bwcLink) && this.def.bwcLink))) {
            href = '#' + app.bwc.buildRoute(module, this.model.id, 'DetailView');
        }
        return href;
    },
    /**
     * Trim whitespace from value if it is a String
     * @param value
     * @return {*}
     */
    unformat: function(value){
        return _.isString(value) ? value.trim() : value;
    }
})
