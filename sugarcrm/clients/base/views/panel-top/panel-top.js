({
    className: "subpanel-header",
    events: {
        "click .btn-invisible": "hidePanel",
        "click a[name=create_button]": "openCreateDrawer",
        "click a[name=select_button]": "openSelectDrawer"
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
    openSelectDrawer: function() {
        var parentModel = this.context.parent.get("model"),
            linkModule = this.context.get("module"),
            link = this.context.get("link"),
            self = this;

        app.drawer.open({
            layout: 'link-selection',
            context: {
                module: linkModule
            }
        }, function(model) {
            if(!model) {
                return;
            }
            var relatedModel = app.data.createRelatedBean(parentModel, model.id, link),
                options = {
                    //Show alerts for this request
                    showAlerts: true,
                    relate: true,
                    success: function(model) {
                        self.context.resetLoadFlag();
                        self.context.set('skipFetch', false);
                        self.context.loadData();
                    },
                    error: function(error) {
                        app.alert.show('server-error', {
                            level: 'error',
                            messages: 'ERR_GENERIC_SERVER_ERROR',
                            autoClose: false
                        });
                    }
                };
            relatedModel.save(null, options);
        });
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
            self.context.set('skipFetch', false);
            self.context.loadData();
        });
    },

    bindDataChange: function() {
        if (this.collection) {
            this.collection.on('reset', this.render, this);
        }
    }
})
