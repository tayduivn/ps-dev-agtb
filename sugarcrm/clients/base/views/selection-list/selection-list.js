({
    extendsFrom: 'BaselistView',
    initialize: function(options) {
        options.meta = options.meta || {};
        options.meta.selection = { type: 'single', label: ' ' };

        app.view.views.BaselistView.prototype.initialize.call(this, options);

        this.context.off("change:selection_model", this._selectModel);
        this.context.on("change:selection_model", this._selectModel, this);
    },
    _selectModel: function() {
        var model = this.context.get("selection_model");
        if (model) {
            app.drawer.close({id: model.id, value: model.get('name')});
            this.context.unset("selection_model", {silent: true});
        }
    }
})
