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

        app.view.Field.prototype.initialize.call(this, options);

        // take advantage of this hook to do the acl check
        // we use this wrapper because our spec
        // requires us to set the button.isHidden = true
        // if we don't render it.
        this.before("render", function() {
            if (self.hasAccess()) {
                this._show();
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
        if (this.isHidden !== false) {
            if (!this.triggerBefore("show")) {
                return false;
            }

            this.getFieldElement().removeClass("hide").show();
            this.isHidden = false;
            this.trigger('show');
        }
    },
    show: function() {
        if(this.hasAccess()) {
            this._show();
        } else {
            this.isHidden = true;
        }
    },
    hide: function() {
        if (this.isHidden !== true) {
            if (!this.triggerBefore("hide")) {
                return false;
            }

            this.getFieldElement().addClass("hide").hide();
            this.isHidden = true;
            this.trigger('hide');
        }
    },
    /**
     * Track using the flag that is set on the hide and show from above.
     *
     * It should check the visivility by isHidden instead of DOM visibility testing
     * since actiondropdown renders its dropdown lazy
     *
     * @returns {boolean}
     */
    isVisible: function() {
        return !this.isHidden;
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
     * Determine if ACLs or other properties (for example, "allow_bwc") allow for the button to show
     * @return {Boolean} true if allow access, false otherwise
     */
    hasAccess: function() {
        // buttons use the acl_action and acl_module properties in metadata to denote their action for acls
        var acl_module = this.def.acl_module,
            acl_action = this.def.acl_action;
        //Need to test BWC status
        if(_.isBoolean(this.def.allow_bwc) && !this.def.allow_bwc){
            var isBwc = app.metadata.getModule(acl_module || this.module).isBwcEnabled;
            if(isBwc){
                return false; //Action not allowed for BWC module
            }
        }
        // Finally check ACLs
        if (!acl_module) {
            return app.acl.hasAccessToModel(acl_action, this.model, this);
        } else {
            return app.acl.hasAccess(acl_action, acl_module);
        }
    }
})
