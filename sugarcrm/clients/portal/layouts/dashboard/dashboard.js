({
    initialize: function(options) {
        // Figure out the modules that are available to the user.
        this.moduleList = app.metadata.getModuleList({visible: true});

        options.meta.components = [];
        // Add components metadata as specified in the module list
        _.each(this.moduleList, function(module) {
            options.meta.components.push({layout: "list", context: {limit: 5, module: module}});
        }, this);

        app.view.Layout.prototype.initialize.call(this, options);
    }
})