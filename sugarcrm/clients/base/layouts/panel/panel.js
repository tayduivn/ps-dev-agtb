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
    className: "filtered tabbable tabs-left",

    // "Hide/Show" state per panel
    HIDE_SHOW_KEY: 'hide-show',
    HIDE_SHOW: {
        HIDE: 'hide',
        SHOW: 'show'
    },

    /**
     * @override
     * @param {Object} opts
     */
    initialize: function(opts) {
        app.view.Layout.prototype.initialize.call(this, opts);

        this.hideShowLastStateKey = app.user.lastState.key(this.HIDE_SHOW_KEY, this);

        this.on("panel:toggle", this.togglePanel, this);
        this.listenTo(this.collection, "reset", function() {
            //Update the subpanel to be open or closed depending on how user left it last
            var hideShowLastState = app.user.lastState.get(this.hideShowLastStateKey);
            if(_.isUndefined(hideShowLastState)) {
                this.togglePanel(this.collection.length > 0, false);
            } else {
                this.togglePanel(hideShowLastState === this.HIDE_SHOW.SHOW, false);
            }
        });
        //Decorate the subpanel based on if the collection is empty or not
        this.listenTo(this.collection, "reset add remove", this._checkIfSubpanelEmpty, this);
    },
    /**
     * Check if subpanel collection is empty and decorate subpanel header appropriately
     * @private
     */
    _checkIfSubpanelEmpty: function(){
        this.$(".subpanel").toggleClass("empty", this.collection.length === 0);
    },
    /**
     * Places layout component in the DOM.
     * @override
     * @param {Component} component
     */
    _placeComponent: function(component) {
        this.$(".subpanel").append(component.el);
        this._hideComponent(component, false);
    },
    /**
     * Toggles panel
     * @param {Boolean} show TRUE to show, FALSE to hide
     * @param {Boolean} saveState(optional) TRUE to save the current state
     */
    togglePanel: function(show, saveState) {
        this.$(".subpanel").toggleClass("closed", !show);
        //check if there's second param then check it and save show/hide to user state
        if(arguments.length === 1 || saveState) {
            app.user.lastState.set(this.hideShowLastStateKey, show ? this.HIDE_SHOW.SHOW : this.HIDE_SHOW.HIDE);
        }
        _.each(this._components, function(component) {
            this._hideComponent(component, show);
        }, this);
    },
    /**
     * Show or hide component except `panel-top`.
     * @param {Component} component
     */
    _hideComponent: function(component, show) {
        if (component.name != "panel-top") {
            if (show) {
                component.show();
            } else {
                component.hide();
            }
        }
    }
})
