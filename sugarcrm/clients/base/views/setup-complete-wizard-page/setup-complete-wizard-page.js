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
