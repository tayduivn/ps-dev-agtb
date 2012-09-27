({
    initialize: function(opts) {
        // TODO: Fix this, right now app.template.getLayout does not retrieve the proper template because
        // it builds the wrong name.
        this.template = app.template.get("l.default");
        this.renderHtml();

        app.view.Layout.prototype.initialize.call(this, opts);

        main = this;

    },

    renderHtml: function() {
        this.$el.html(this.template(this));
    },

    addComponent: function(component, def) {
        app.view.Layout.prototype.addComponent.call(this, component, def);
    },

    _placeComponent: function(component) {
        app.view.Layout.prototype._placeComponent.call(this, component);
    }
})