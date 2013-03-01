({
    className: "subpanel-header",
    events: {
        "click .btn-invisible": "hidePanel",
        "click a[name=create_button]": "openCreateDrawer"
    },

    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);
    },

    hidePanel: function(e) {
        var target = this.$(e.currentTarget),
            data = target.data();

        this.layout.trigger("hide", data.visible);
        target.data("visible", !data.visible);
    },

    openCreateDrawer: function() {
        var parentModel = this.context.parent.get("model"),
            link = this.context.get("link"),
            model = app.data.createRelatedBean(parentModel, null, link),
            relatedFields = app.data.getRelateFields(parentModel.module, link);

        if(!_.isUndefined(relatedFields)) {
            _.each(relatedFields, function(field) {
                model.set(field.name, parentModel.get(field.rname));
                model.set(field.id_name, parentModel.get("id"));
            }, this);
        }

        app.drawer.open({
            layout: 'create',
            context: {
                create: true,
                module: model.module,
                model: model
            }
        });

        model.on("sync", function(model) {
            this.collection.add(model);
            this.collection.trigger("reset");
            model.off("sync");
        }, this);
    },

    bindDataChange: function() {
        if (this.collection) {
            this.collection.on('reset', this.render, this);
        }
    }
})
