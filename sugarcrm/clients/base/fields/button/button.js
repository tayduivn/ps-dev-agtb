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
        var self = this;
        this.events = _.extend({}, this.events, options.def.events, {
            'click .disabled' : 'preventClick'
        });
        var origRender = this._render;
        this._render = _.debounce(function(){
            //Because of throttle, calls to render may come in after dispose has been called.
            if (this.disposed) return;
            return origRender.call(this);
        }, 100);
        app.view.Field.prototype.initialize.call(this, options);

        // take advantage of this hook to do the acl check
        // we use this wrapper because our spec
        // requires us to set the button.isHidden = true
        // if we don't render it.
        this.before("render", function() {
            if (self.hasAccess()) {
                return true;
            }
            else {
                this.hide();
                return false;
            }
        });
    },
    _render:function(){
        this.full_route = _.isString(this.def.route) ? this.def.route : null;

        app.view.Field.prototype._render.call(this);
        if (this.isHidden) {
            this.hide();
        } else {
            this._show();
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
    /**
     * Handles the jquery showing and event throwing
     * of the button. does no access checks.
     * @protected
     */
    _show: function() {
        app.view.Field.prototype.show.call(this);
        this.isHidden = false;
        this.trigger("show");
    },
    show: function() {
        if(this.hasAccess()) {
            this._show();
        }
    },
    hide: function() {
        app.view.Field.prototype.hide.call(this);
        this.isHidden = true;
        this.trigger("hide");
    },
    /**
     * {@inheritdoc}
     *
     * No data changes to bind.
     */
    bindDomChange: function () {
    },
    /**
     * {@inheritdoc}
     *
     * No need to bind DOM changes to a model.
     */
    bindDataChange: function () {
    },
    /**
     * Determine if ACLs allow for the button to show
     * @return {Boolean} true if ACLs allow access, false otherwise
     */
    hasAccess: function() {
        // buttons use the acl_action and acl_module properties in metadata to denote their action for acls
        var acl_module = this.def.acl_module,
            acl_action = this.def.acl_action;
        if (!acl_module) {
            return app.acl.hasAccessToModel(acl_action, this.model, this);
        } else {
            return app.acl.hasAccess(acl_action, acl_module);
        }
    }
})
