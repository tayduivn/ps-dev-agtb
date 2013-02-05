({
    events: {
        "click .closeSubdetail": "hidePreviewPanel"
    },

    initialize: function(opts) {
        // TODO: Fix this, right now app.template.getLayout does not retrieve the proper template because
        // it builds the wrong name.
        this.template = app.template.get("l.default");
        this.renderHtml();

        app.view.Layout.prototype.initialize.call(this, opts);
        this.processDef();

        app.events.on("preview:open", this.showPreviewPanel, this);
        app.events.on("preview:close", this.hidePreviewPanel, this);
        this.context.on("toggleSidebar", this.toggleSide, this);
    },

    toggleSide: function() {
        this.$('.main-pane').toggleClass('span12');
        this.$('.main-pane').toggleClass('span8');
        this.$('.side').toggle();
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

    showPreviewPanel: function() {
        this.$(".side-pane").removeClass("active");
        this.$(".preview-pane").addClass("active");
    },

    hidePreviewPanel: function() {
        this.$(".preview-pane").removeClass("active");
        this.$(".side-pane").addClass("active");
    },
})