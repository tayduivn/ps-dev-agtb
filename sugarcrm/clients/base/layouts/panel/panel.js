({
    className: "filtered tabbable tabs-left",

    initialize: function(opts) {
        app.view.Layout.prototype.initialize.call(this, opts);

        this.on("hide", this.toggleChevron, this);

        this.listenTo(this.collection, "reset", function() {
            if (this.collection.length === 0) {
                this.trigger('hide', false);
            } else {
                this.trigger('hide', true);
            }
        });
    },

    _placeComponent: function(component) {
        this.$(".subpanel").append(component.el);
        if (component.name != "panel-top") {
            component.hide();
        }
    },

    toggleChevron: function(e) {
        this.$(".subpanel").toggleClass("out", e);
    }
})
