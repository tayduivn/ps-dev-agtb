({
    extendsFrom: 'HeaderpaneView',
    linkModule: null,
    link: null,
    initialize: function(options) {
        this.events = _.extend({}, this.events || {}, {
            'click [name=create_button]' : 'createClicked',
            'click [name=cancel_button]' : 'cancelClicked'
        });
        app.view.views.HeaderpaneView.prototype.initialize.call(this, options);
        this.context.on("link:module:select", this.setModule, this);
    },
    setModule: function(meta) {
        if(meta) {
            this.linkModule = meta.module;
            this.link = meta.link;
        } else {
            this.linkModule = null;
            this.link = null;
        }

    },
    _dispose: function() {
        this.context.off("link:module:select", null, this);
        app.view.View.prototype._dispose.call(this);
    },
    createLinkModel: function(link) {
        var parentModel = this.model,
            model = app.data.createRelatedBean(this.model, null, link),
            relatedFields = app.data.getRelateFields(this.module, link);

        if(!_.isEmpty(relatedFields)) {
            _.each(relatedFields, function(field) {
                model.set(field.name, parentModel.get(field.rname));
                model.set(field.id_name, parentModel.get("id"));
            }, this);
        }

        return model;
    },
    createClicked: function() {
        if(_.isEmpty(this.link)) {
            app.alert.show('invalid-data', {
                level: 'error',
                messages: app.lang.get('ERROR_EMPTY_LINK_MODULE'),
                autoClose: true
            });
            return;
        }

        var model = this.createLinkModel(this.link);

        app.drawer.open({
            layout: 'create',
            context: {
                module: model.module,
                model: model,
                create: true
            }
        }, function(model) {
            if(!model) {
                return;
            }
            app.drawer.close(model);
        });
    },
    cancelClicked: function() {
        app.drawer.close();
    }
})
