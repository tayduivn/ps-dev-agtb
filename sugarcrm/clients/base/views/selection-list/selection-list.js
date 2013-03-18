({
    /**
     * @class View.SelectionListView
     * @alias SUGAR.App.view.views.SelectionListView
     * @extends View.FlexListView
     */
    extendsFrom: 'FlexListView',
    initialize: function(options) {
        options.meta = options.meta || {};
        options.meta.selection = { type: 'single', label: ' ' };

        app.view.views.FlexListView.prototype.initialize.call(this, options);

        this.context.off("change:selection_model", this._selectModel);
        this.context.on("change:selection_model", this._selectModel, this);
    },
    _selectModel: function() {
        var model = this.context.get("selection_model");
        if (model) {
            var attributes = {
                id: model.id,
                value: model.get('name')
            };
            _.each(model.attributes, function(value, field) {
                if(app.acl.hasAccessToModel('view', model, field)) {
                    attributes[field] = attributes[field] || model.get(field);
                }
            }, this);
            app.drawer.close(attributes);
            this.context.unset("selection_model", {silent: true});
        }
    }
})
