({
    className: "row-fluid",
    initialize: function(opts) {
        app.view.Layout.prototype.initialize.call(this, opts);
        this.processDef();
        this.context.on("toggleSidebar", this.toggleSide, this);
        this.context.on("openSidebar", this.openSide, this);
    },
    toggleSide: function() {
        this.$('.main-pane').toggleClass('span12').toggleClass('span8');
        this.$('.side').toggle();
        app.controller.context.trigger("toggleSidebarArrows");
    },
    openSide: function() {
        this.$('.main-pane').addClass('span8').removeClass('span12');
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
    },

    /**
     * Get the width of either the main or side pane depending upon where the
     * component resides.
     * @param {Backbone.Component} component
     * @returns {number}
     */
    getPaneWidth: function(component) {
        var paneSelectors = ['.main-pane', '.side'],
            pane = _.find(paneSelectors, function(selector) {
                return ($.contains(this.$(selector).get(0), component.el));
            }, this);

        return this.$(pane).width() || 0;
    }
})
