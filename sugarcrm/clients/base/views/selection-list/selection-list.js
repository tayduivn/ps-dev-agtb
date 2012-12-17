({
    extendsFrom: 'BaselistView',
    initialize: function(options) {
        options.meta = options.meta || {};
        options.meta.selection = { type: 'single', label: ' ' };
        app.view.views.BaselistView.prototype.initialize.call(this, options);
    },
    _render: function() {
        app.view.views.BaselistView.prototype._render.call(this);
        this.context.off("change:selection_model", null, this);
        this.context.on("change:selection_model", function() {
            this.context.parent.trigger("drawer:callback", this.context.get("selection_model"));
        }, this)
    }

})