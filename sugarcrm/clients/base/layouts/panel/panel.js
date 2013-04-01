({
    className: "filtered tabbable tabs-left",

    initialize: function(opts) {
        app.view.Layout.prototype.initialize.call(this, opts);

        this.bind("hide", this.toggleChevron);

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
    },

    toggleChevron: function(e) {
        this.$(".subpanel").toggleClass("out", e);
    }
})
