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
    tagName: "span",
    fieldTag: "a",
    initialize: function(options) {
        this.events = _.extend({}, this.events, options.def.events, {
            'click .disabled' : 'preventClick'
        });
        app.view.Field.prototype.initialize.call(this, options);
        this._render = _.throttle(app.view.fields.ButtonField.prototype._render, 100);
        this.model.on('data:sync:end', this.render, this);
    },
    _render:function(){
        // buttons use the acl_action and acl_module properties in metadata to denote their action for acls
        var acl_module = this.def.acl_module,
            acl_action = this.def.acl_action,
            hasAccess;

        this.full_route = _.isString(this.def.route) ? this.def.route : null;

        if (!acl_module) {
            hasAccess = app.acl.hasAccessToModel(acl_action, this.model, this);
        } else {
            hasAccess = app.acl.hasAccess(acl_action, acl_module);
        }

        app.view.Field.prototype._render.call(this);
        if (!hasAccess  || this.isHidden) {
            this.hide();
        } else {
            this.show();
        }
    },
    getFieldElement: function() {
        return this.$(this.fieldTag);
    },
    setDisabled: function(disable) {
        disable = _.isUndefined(disable) ? true : disable;

        this.def.css_class = this.def.css_class || '';
        var css_class = this.def.css_class.split(' ');
        if(disable) {
            css_class.push('disabled');
        } else {
            css_class = _.without(css_class, 'disabled');
        }
        this.def.css_class = _.unique(_.compact(css_class)).join(' ');
        app.view.Field.prototype.setDisabled.call(this, disable);
    },
    preventClick: function(evt) {
        if(this.isDisabled()) {
            return false;
        }
    },
    show: function() {
        app.view.Field.prototype.show.call(this);
        this.isHidden = false;
        this.trigger("show");
    },
    hide: function() {
        app.view.Field.prototype.hide.call(this);
        this.isHidden = true;
        this.trigger("hide");
    }
})
