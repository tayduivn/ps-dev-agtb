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
    className: "row-fluid",
    initialize: function(opts) {
        app.view.Layout.prototype.initialize.call(this, opts);
        this.processDef();
        this.context.on("toggleSidebar", this.toggleSide, this);
        this.context.on("openSidebar", this.openSide, this);
    },
    toggleSide: function() {
        this.$('.main-pane').toggleClass('span12').toggleClass('span8');
        this.$('.side').toggle();
        app.controller.context.trigger("toggleSidebarArrows");
    },
    openSide: function() {
        this.$('.main-pane').addClass('span8').removeClass('span12');
        this.$('.side').show();
        app.controller.context.trigger("openSidebarArrows");
    },
    processDef: function() {
        this.$(".main-pane").addClass("span" + this.meta.components[0]["layout"].span);
        this.$(".side").addClass("span" + this.meta.components[1]["layout"].span);
    },
    renderHtml: function() {
        this.$el.html(this.template(this));
    },
    _placeComponent: function(component) {
        if (component.meta.name) {
            this.$("." + component.meta.name).append(component.$el);
        }
    },

    /**
     * Get the width of either the main or side pane depending upon where the
     * component resides.
     * @param {Backbone.Component} component
     * @returns {number}
     */
    getPaneWidth: function(component) {
        if (!this.$el) {
            return 0;
        }
        var paneSelectors = ['.main-pane', '.side'],
            pane = _.find(paneSelectors, function(selector) {
                return ($.contains(this.$(selector).get(0), component.el));
            }, this);

        return this.$(pane).width() || 0;
    }
})
