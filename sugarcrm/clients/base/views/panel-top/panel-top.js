({
    className: "subpanel-header",
    events: {
        "click .btn-invisible": "hidePanel"
    },

    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);
    },

    hidePanel: function(e) {
        var target = this.$(e.currentTarget),
            data = target.data();

        this.layout.trigger("hide", data.visible);

        (!data.visible) ? target.data("visible", true) : target.data("visible", false);
    }
})
