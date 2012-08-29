({
    initialize: function(options) {
        // Figure out the modules that are available to the user.
        this.module_list = app.metadata.getModuleNames(true);

        options.meta.components = [];
        // Add components metadata as specified in the module list
        _.each(this.module_list, function(module) {
            options.meta.components.push({layout: "list", context: {limit: 5, module: module}});
        }, this);

        app.view.Layout.prototype.initialize.call(this, options);
    }
})
