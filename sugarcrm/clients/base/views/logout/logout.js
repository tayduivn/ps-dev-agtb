({
    /**
     * Login form view.
     * @class View.Views.LogoutView
     * @alias SUGAR.App.view.views.LogoutView
     */
    events: {
        "click [name=login_button]": "login",
        "click [name=login_form_button]": "login_form",
    },

    /**
     * @override
     * @private
     */
    _render: function() {
        this.logoUrl = app.metadata.getLogoUrl();
        app.view.View.prototype._render.call(this);
        this.refreshAddtionalComponents();
        return this;
    },

    /**
     * Refresh additional components
     */
    refreshAddtionalComponents: function() {
        _.each(app.additionalComponents, function(component) {
            component.render();
        });
    },

    /**
     * Process Login
     */
    login: function() {
    	app.router.login();
    },
    
    /**
     * Show Login form
     */
    login_form: function() {
        app.controller.loadView({
            module: "Login",
            layout: "login",
            create: true
        });
    }
})
