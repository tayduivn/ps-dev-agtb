({
    _addComponentsFromDef: function(components) {
        this.$el.html(this.template(this));
        app.view.Layout.prototype._addComponentsFromDef.call(this, components);
    },
    _placeComponent: function(comp, def) {
        this.$(".widget-content:first").append(comp.el);
    }
})
