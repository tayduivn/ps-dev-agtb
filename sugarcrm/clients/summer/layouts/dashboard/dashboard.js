({
    initialize: function(options) {
        // Figure out the modules that are available to the user.
        this.moduleList = app.metadata.getModuleNames(true);

        app.view.Layout.prototype.initialize.call(this, options);
    }
})