({
    extendsFrom: 'BaselistView',
    initialize: function(options) {
        options.meta = options.meta || {};
        options.meta.selection = { type: 'single', label: ' ' };
        app.view.views.BaselistView.prototype.initialize.call(this, options);
    },
    _renderHtml: function() {
        app.view.views.BaselistView.prototype._renderHtml.call(this);
        this.context.off("change:selection_model", null, this);
        this.context.on("change:selection_model", function() {
            var model = this.context.get("selection_model");
            if(model) {
                this.context.parent.trigger("drawer:callback", {id: model.id, value: model.get('name')});
                this.context.set("selection_model", null, {silent: true});
            }
        }, this)
    }
})