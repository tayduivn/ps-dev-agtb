({
    initialize: function(opts) {
        _.bindAll(this);
        this.template = app.template.get("l.activitystream");
        this.renderHtml();

        app.view.Layout.prototype.initialize.call(this, opts);
    },

    renderHtml: function() {
        this.$el.html(this.template(this));
    },

    _placeComponent: function(component) {
        this.$el.find(".activitystream-list").append(component.el);
    }
})