({
    /**
     * Header section for Subpanel layouts
     *
     * @class View.SubpanelHeaderView
     * @alias SUGAR.App.view.views.SubpanelHeaderView
     */
    className: "subpanel-header",
    events: {
        "click": "togglePanel",
        "click a[name=create_button]:not('.disabled')": "createRelatedRecord",
        "click a[name=select_button]:not('.disabled')": "openSelectDrawer"
    },

    /**
     * @override
     * @param opts
     */
    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);
        // This is in place to get the lang strings from the right module. See
        // if there is a better way to do this later.
        this.parentModule = this.context.parent.get("module");
    },


    /**
    * Event handler that closes the subpanel layout when the SubpanelHeader is clicked
    * @param e DOM event
    */
    togglePanel: function(e) {
        // Make sure we aren't toggling the panel when the user clicks on a dropdown action.
        var toggleSubpanel = !$(e.target).parents("span.actions").length;
        if (toggleSubpanel) {
            this._toggleSubpanel();
        }
    },

    _toggleSubpanel: function() {
        var currentlyVisible = this.layout.$(".subpanel").hasClass("out");
        this.layout.trigger("hide", !currentlyVisible);
    },

    /**
     * Event handler for the select button that opens a link selection dialog in a drawer for linking
     * an existing record
     */
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
    createLinkModel: function(parentModel, link) {
        var model = app.data.createRelatedBean(parentModel, null, link),
            relatedFields = app.data.getRelateFields(parentModel.module, link);

        if(!_.isEmpty(relatedFields)) {
            model._defaults = model._defaults || {};

            _.each(relatedFields, function(field) {
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

    /**
     * Event handler for the create button that launches UI for creating a related record for this subpanel
     * For sidecar modules, this means a drawer is opened with the create dialog inside.
     * For BWC modules, this means we trigger a create route to enter BWC mode
     */
    createRelatedRecord: function() {
        var moduleMeta = app.metadata.getModule(this.module);
        if(moduleMeta && moduleMeta.isBwcEnabled){
            this.routeToBwcCreate();
        } else {
            this.openCreateDrawer();
        }
    },

    /**
     * Route to Create Related record UI for a BWC module
     */
    routeToBwcCreate: function() {
        var parentModel = this.context.parent.get("model");
        var link = this.context.get("link");
        app.bwc.createRelatedRecord(this.module, parentModel, link);
    },

    /**
     * For sidecar modules, we create new records by launching a create drawer UI
     */
    openCreateDrawer: function() {
        var parentModel = this.context.parent.get("model"),
            link = this.context.get("link"),
            model = this.createLinkModel(parentModel, link);
        var self = this;
        app.drawer.open({
            layout: 'create-actions',
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

    /**
     * @override
     */
    bindDataChange: function() {
        if (this.collection) {
            this.listenTo(this.collection, 'reset', this.render);
        }
    }
})
