({
    events: {
        "click .closeSubdetail": "hidePreview"
    },

    initialize: function(opts) {
        // TODO: Fix this, right now app.template.getLayout does not retrieve the proper template because
        // it builds the wrong name.
        this.template = app.template.get("l.default");
        this.renderHtml();

        app.view.Layout.prototype.initialize.call(this, opts);
        this.processDef();

        this.context.on("togglePreview", function(model) { this.showPreview(); }, this);
    },

    processDef: function() {
        this.$(".main-pane").addClass("span" + this.meta.components[0]["layout"].span);
        this.$(".side").addClass("span" + this.meta.components[1]["layout"].span);
    },

    renderHtml: function() {
        this.$el.html(this.template(this));
    },

    addComponent: function(component, def) {
        if (def.layout) {
            def.layout.parentLayout = this;
        }

        app.view.Layout.prototype.addComponent.call(this, component, def);
    },

    _placeComponent: function(component) {
        if (component.meta.name) {
            this.$("." + component.meta.name).append(component.$el);
        }
    },

    showPreview: function() {
        this.togglePreview();
    },

    hidePreview: function() {
        this.togglePreview();
    },

    togglePreview: function() {
        this.$(".side-pane").toggleClass("hide");
        this.$(".preview-pane").toggleClass("hide");
    }
})