({
    extendsFrom: 'HeaderpaneView',
    linkModule: null,
    link: null,
    initialize: function (options) {
        this.events = _.extend({}, this.events || {}, {
            'click [name=create_button]': 'createClicked',
            'click [name=cancel_button]': 'cancelClicked',
            'click [name=select_button]': 'selectClicked'
        });
        this.action = options.meta.action;
        var meta = app.metadata.getView(null, options.name);

        options.meta = _.extend({type: 'headerpane'}, options.meta, meta[this.action]);

        app.view.views.HeaderpaneView.prototype.initialize.call(this, options);
        this.context.on("link:module:select", this.setModule, this);
    },
    setModule: function (meta) {
        if (meta) {
            this.linkModule = meta.module;
            this.link = meta.link;
        } else {
            this.linkModule = null;
            this.link = null;
        }

    },
    _dispose: function () {
        this.context.off("link:module:select", null, this);
        app.view.views.HeaderpaneView.prototype._dispose.call(this);
    },

    /**
     * Create a new linked Bean model which is related to the parent bean model
     * It populates related fields from the parent bean model attributes
     * All related fields are defined in the relationship metadata
     *
     * If the related field contains the auto-populated fields,
     * it also copies the auto-populate fields
     *
     * @param {Model} Parent Bean Model
     * @param {String} name of relationship link
     */
    createLinkModel: function (parentModel, link) {
        var parentModel = this.model,
            model = app.data.createRelatedBean(parentModel, null, link),
            relatedFields = app.data.getRelateFields(parentModel.module, link);

        if (!_.isEmpty(relatedFields)) {
            _.each(relatedFields, function (field) {
                model.set(field.name, parentModel.get(field.rname));
                model.set(field.id_name, parentModel.get("id"));
                model._defaults[field.name] = model.get(field.name);
                model._defaults[field.id_name] = model.get(field.id_name);

                if(field.populate_list) {
                    _.each(field.populate_list, function (target, source) {
                        source = _.isNumber(source) ? target : source;
                        if (!_.isUndefined(parentModel.get(source)) && app.acl.hasAccessToModel('edit', model, target)) {
                            model.set(target, parentModel.get(source));
                            model._defaults[target] = model.get(target);
                        }
                    }, this);
                }
            }, this);
        }

        return model;
    },
    selectClicked: function () {
        if (_.isEmpty(this.link)) {
            app.alert.show('invalid-data', {
                level: 'error',
                messages: app.lang.get('ERROR_EMPTY_LINK_MODULE'),
                autoClose: true
            });
            return;
        }

        var parentModel = this.model,
            module = app.data.getRelatedModule(this.model.module, this.link),
            link = this.link,
            self = this;

        app.drawer.open({
            layout: 'link-selection',
            context: {
                module: module
            }
        }, function (model) {
            if (!model) {
                return;
            }
            var relatedModel = app.data.createRelatedBean(parentModel, model.id, link),
                options = {
                    //Show alerts for this request
                    showAlerts: true,
                    relate: true,
                    success: function (model) {
                        app.drawer.close(self.context, model);
                    },
                    error: function (error) {
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
    createClicked: function () {
        if (_.isEmpty(this.link)) {
            app.alert.show('invalid-data', {
                level: 'error',
                messages: app.lang.get('ERROR_EMPTY_LINK_MODULE'),
                autoClose: true
            });
            return;
        }

        var model = this.createLinkModel(this.model, this.link);

        app.drawer.open({
            layout: 'create-actions',
            context: {
                module: model.module,
                model: model,
                create: true
            }
        }, function (context, model) {
            if (!model) {
                return;
            }
            app.drawer.close(context, model);
        });
    },
    cancelClicked: function () {
        app.drawer.close();
    }
})
