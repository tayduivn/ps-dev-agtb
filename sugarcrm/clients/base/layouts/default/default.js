({
    className: "row-fluid",
    initialize: function(opts) {
        // TODO: Fix this, right now app.template.getLayout does not retrieve the proper template because
        // it builds the wrong name.
        this.template = app.template.get("l.default");
        this.renderHtml();

        app.view.Layout.prototype.initialize.call(this, opts);
        this.processDef();

        this.context.on("toggleSidebar", this.toggleSide, this);
        this.context.on("openSidebar", this.openSide, this);
    },

    toggleSide: function() {
        this.$('.main-pane').toggleClass('span12');
        this.$('.main-pane').toggleClass('span8');
        this.$('.side').toggle();
    },
    openSide: function() {
        this.$('.main-pane').addClass('span8');
        this.$('.main-pane').removeClass('span12');
        this.$('.side').show();
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
    }
})
