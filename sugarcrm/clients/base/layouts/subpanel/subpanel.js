({
    events: {
        "click .toolbar-btns a": "toggleView"
    },

    initialize: function(opts) {
        _.bindAll(this);

        this.template = app.template.get("l.subpanel");
        this.renderHtml();

        app.view.Layout.prototype.initialize.call(this, opts);
    },

    renderHtml: function() {
        this.$el.html(this.template(this));
        if (this.options.context.has("modelId")) {
            this.$(".tabbable").toggleClass("hide");
        }
    },

    toggleView: function(e) {
        var data = this.$(e.currentTarget).data();

        this.showComponent(data.view);
        e.preventDefault();
    },

    showComponent: function(name) {
        _.each(this._components, function(comp) {
            if (comp.name == name) {
                comp.show();
            } else {
                comp.hide();
            }
        }, this);
    }
})