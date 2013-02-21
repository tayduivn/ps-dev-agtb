({
    className: "filtered tabbable tabs-left",

    initialize: function(opts) {
        app.view.Layout.prototype.initialize.call(this, opts);

        this.bind("hide", this.toggleChevron);
    },

    _placeComponent: function(component) {
        this.$(".subpanel").append(component.el);
    },

    toggleChevron: function(e) {
        this.$(".subpanel").toggleClass("out");
    }
})
