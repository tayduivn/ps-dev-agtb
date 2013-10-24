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
    extendsFrom: 'button',

    events: {
        'click .drawerTrig': 'toggle' //ensure "hit area" big enough
    },
    _render: function() {
        app.view.Field.prototype._render.call(this);
        // Broadcast when we've fully rendered sidebar toggle
        app.controller.context.trigger("sidebarRendered");
    },
    bindDataChange:function () {
        // These corresponding to the toggleSide & openSide events in default layout
        app.controller.context.on("toggleSidebarArrows", this.updateArrows, this);
        app.controller.context.on("openSidebarArrows", this.sidebarArrowsOpen, this);
    },
    updateArrows: function() {
        var chevron = this.$('.drawerTrig i'),
            pointRightClass = 'icon-double-angle-right';
        if (chevron.hasClass(pointRightClass)) {
            this.updateArrowsWithDirection('close');
        } else {
            this.updateArrowsWithDirection('open');
        }
    },
    sidebarArrowsOpen: function() {
        this.updateArrowsWithDirection('open');
    },
    updateArrowsWithDirection: function(state) {
        var chevron = this.$('.drawerTrig i'),
            pointRightClass = 'icon-double-angle-right',
            pointLeftClass = 'icon-double-angle-left';
        if (state === 'open') {
            chevron.removeClass(pointLeftClass).addClass(pointRightClass);
            app.events.trigger('app:toggle:sidebar', 'open');
        } else if (state === 'close') {
            chevron.removeClass(pointRightClass).addClass(pointLeftClass);
            app.events.trigger('app:toggle:sidebar', 'close');
        } else {
            app.logger.warn("updateArrowsWithDirection called with invalid state; should be 'open' or 'close', but was: "+state)
        }
    },
    // If toggled from a user clicking on anchor simply trigger toggleSidebar
    toggle: function() {
        this.context.trigger('toggleSidebar');
        //toggling sidebar can affect the width of content in the same way as a window resize
        //notify of a window resize so that any content listening for a resize can react in the same way for this sidebar toggle
        $(window).trigger('resize');
    },
    _dispose: function () {
        app.view.invokeParent(this, {type: 'field', name: 'button', method: '_dispose'});
        app.controller.context.off(null, null, this);//remove all events for context `this`
    }
})
