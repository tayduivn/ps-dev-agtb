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
     * Setup Complete wizard page for the FirstLoginWizard
     * @class View.Views.SetupCompleteWizardPageView
     * @alias SUGAR.App.view.views.SetupCompleteWizardPageView
     */
    extendsFrom: "WizardPageView",
    /**
     * Name of wizard being displayed
     */
    wizardName : "",
    /**
     * Set flag for admin or user wizard so we can render the correct template
     * @override
     * @param options
     */
    initialize: function(options){
        //Extend default events to add listener for click events on links
        this.events = _.extend({}, this.events, {
            "click a.thumbnail": "linkClicked",
            "click [name=start_sugar_button]:not(.disabled)": "next"
        });
        app.view.invokeParent(this, {type: 'view', name: 'wizard-page', method: 'initialize', args:[options]});
        this.wizardName = this.context.get("wizardName") || "user";
    },
    /**
     * @override
     * @returns {boolean}
     */
    isPageComplete: function(){
        return true;
    },
    /**
     * Event handler whenever a link is clicked that makes sure wizard is finished
     * We need to use app router for Sugar app links on complete page.
     * External links should always open onto new pages.
     * @param ev
     */
    linkClicked: function(ev){
        var href, redirectUrl,
            target = this.$(ev.currentTarget);
        if(this.$(target).attr("target") !== "_blank") {
            ev.preventDefault();
            //Show the header bar since it is likely hidden
            $("#header").show();
            href = this.$(target).attr("href");
            // Check if bwc link; if so, we need to do bwc.login first
            if (href.indexOf('#bwc/') === 0) {
                redirectUrl = href.split('#bwc/')[1];
                app.bwc.login(redirectUrl);
            } else {
                // Not bwc, so use router navigate instead
                app.router.navigate($(ev.currentTarget).attr("href"), {trigger: true});
            }
        }
    },
    /**
     * When the setup complete page is shown, we know we can update user object
     * now that user setup is complete so that routing to setup wizard stops.
     */
    _render: function() {
        this._super('_render');
        app.user.unset("show_wizard");
    }

})
