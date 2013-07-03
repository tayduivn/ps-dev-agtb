({
    /**
     * Login form view.
     * @class View.Views.LoginView
     * @alias SUGAR.App.view.views.LoginView
     */
    events: {
        "click [name=login_button]": "login",
        "click [name=login_form_button]": "login_form",
    },
    
    /**
     * Get the fields metadata from panels and declare a Bean with the metadata attached
     * @param meta
     * @private
     */
    _declareModel: function(meta) {
        app.data.declareModel('Login');
    },
    
    /**
     * @override
     * @param options
     */
    initialize: function(options) {
        // Declare a Bean so we can process field validation
        this._declareModel(options.meta);

        // Reprepare the context because it was initially prepared without metadata
        options.context.prepare(true);

        app.view.View.prototype.initialize.call(this, options);
    },

    /**
     * @override
     * @private
     */
    _render: function() {
        app.view.View.prototype._render.call(this);
    },
    
    /**
     * Process Login
     */
    login: function() {
    	app.router.login();
    },
    
    /**
     * Process Login
     */
    login_form: function() {
        app.controller.loadView({
            module: "Login",
            layout: "login",
            create: true
        });
    }

})
