({
    className: "row-fluid",
    initialize: function(opts) {
        app.view.Layout.prototype.initialize.call(this, opts);
        this.processDef();
        this.context.on("toggleSidebar", this.toggleSide, this);
        this.context.on("openSidebar", this.openSide, this);
    },
    toggleSide: function() {
        this.$('.main-pane').not("#dashboard,.dashboard").toggleClass('span12');
        this.$('.main-pane').not("#dashboard,.dashboard").toggleClass('span8');
        this.$('.side').toggle();
        app.controller.context.trigger("toggleSidebarArrows");
    },
    openSide: function() {
        this.$('.main-pane').not("#dashboard,.dashboard").addClass('span8');
        this.$('.main-pane').not("#dashboard,.dashboard").removeClass('span12');
        this.$('.side').show();
        app.controller.context.trigger("openSidebarArrows");
    },
    processDef: function() {
        this.$(".main-pane").addClass("span" + this.meta.components[0]["layout"].span);
        this.$(".side").addClass("span" + this.meta.components[1]["layout"].span);
    },
    renderHtml: function() {
        this.$el.html(this.template(this));
    },
    _placeComponent: function(component) {
        if (component.meta.name) {
            this.$("." + component.meta.name).append(component.$el);
        }
    }
})
