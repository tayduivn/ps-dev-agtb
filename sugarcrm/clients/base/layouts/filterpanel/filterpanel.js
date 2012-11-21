({
    events: {
        "click .toolbar-btns a": "toggleView"
    },

    initialize: function(opts) {
        _.bindAll(this);

        this.processMeta();
        this.template = app.template.get("l.filterpanel");
        this.renderHtml();

        app.view.Layout.prototype.initialize.call(this, opts);
    },

    processMeta: function() {
        this.tabs = this.options.meta.tabs;
    },

    renderHtml: function() {
        this.$el.html(this.template(this));
        if (this.options.context.has("modelId") && !_.isEmpty(this.tabs)) {
            this.$(".tabbable").toggleClass("hide");
        }
    },

    _placeComponent: function(component) {
        this.$el.append(component.el);

        if (this.options.meta.defaultToggle && (component.name !== this.options.meta.defaultToggle)) {
            component.hide();
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