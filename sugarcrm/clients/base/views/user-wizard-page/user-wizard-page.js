({
    /**
     * User Profile wizard page for the FirstLoginWizard
     * @class View.Views.UserWizardPageView
     * @alias SUGAR.App.view.views.UserWizardPageView
     */
    extendsFrom: "WizardPageView",
    /**
     * @override
     * @param options
     */
    initialize: function(options){
        //Load the default wizard page template, if you want to.
        options.template = app.template.getView("wizard-page");
        app.view.invokeParent(this, {type: 'view', name: 'wizard-page', method: 'initialize', args:[options]});
    },
    /**
     * @override
     * @returns {boolean}
     */
    isPageComplete: function(){
        return (Math.random()*2) > 1;
    },
    /**
     * @override
     */
    finish: function(){

    }
})
