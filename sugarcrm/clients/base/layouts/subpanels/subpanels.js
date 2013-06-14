({
    initialize: function(opts) {
        if (!opts.meta) return;

        app.view.Layout.prototype.initialize.call(this, opts);
        this.layout.on("subpanel:change", this.showSubpanel, this);
    },

    showSubpanel: function(linkName) {
        var self = this,
            cacheKey = "subpanels:last:" + self.module;
        if (linkName) {
            app.cache.set(cacheKey, linkName);
        }
        _.each(this._components, function(component) {
            if(!linkName || linkName === component.context.get("link")) {
                component.context.set("hidden", false);
                component.show();
            } else {
                component.context.set("hidden", true);
                component.hide();
            }
        });
    }
})
