({
    className: "subpanel-header",
    events: {
        "click .btn-invisible": "hidePanel",
        "click a[name=create_button]": "openCreateDrawer"
    },

    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);
        // This is in place to get the lang strings from the right module. See
        // if there is a better way to do this later.
        this.parentModule = this.context.parent.get("module");
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
        var self = this;
        app.drawer.open({
            layout: 'create',
            context: {
                create: true,
                module: model.module,
                model: model
            }
        }, function(model) {
            if(!model) {
                return;
            }

            self.context.resetLoadFlag();
            self.context.loadData();
        });
    },

    bindDataChange: function() {
        if (this.collection) {
            this.collection.on('reset', this.render, this);
        }
    }
})
