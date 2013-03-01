({
    initialize: function(opts) {
        app.view.Layout.prototype.initialize.call(this, opts);
        this.layout.on("subpanel:change", this.showSubpanel, this);
    },

    showSubpanel: function(linkName) {
        _.each(this._components, function(component) {
            if(!linkName || linkName === component.context.get("link")) {
                component.show();
            } else {
                component.hide();
            }
        });
    }
})
