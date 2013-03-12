({
    initialize: function(opts) {
        if (!opts.meta) return;

        _.each(opts.meta.components, function(component) {
            component.context = component.context || {};
            component.context.hideAlertsOn = "read";
        });
        app.view.Layout.prototype.initialize.call(this, opts);
        this.layout.on("subpanel:change", this.showSubpanel, this);
    },

    showSubpanel: function(linkName) {
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
