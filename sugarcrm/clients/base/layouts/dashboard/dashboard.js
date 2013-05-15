/**
 * @class BaseDashboardLayout
 * @extends app.view.Layout
 *
 * The outer layout of the dashboard.
 * This layout contains the header view and wraps the daslet-main layout.
 * The layouts for each dashboard are stored in the dashboard endpoint (rest/v10/Dashboards/{id})
 *
 */
({
    toggled: false,
    className: 'row-fluid',
    initialize: function (options) {

        var context = options.context,
            module = context.parent ? context.parent.get("module") : context.get("module"),
            view = context.parent ? context.parent.get("layout") : '',
            sync = function (method, model, options) {
                options = app.data.parseOptionsForSync(method, model, options);
                var callbacks = app.data.getSyncCallbacks(method, model, options),
                    path = (this.dashboardModule === 'Home' || model.id) ? this.apiModule : this.apiModule + '/' + this.dashboardModule;
                if (method === 'read') {
                    options.params.view = view;
                }
                app.api.records(method, path, model.attributes, options.params, callbacks);
            },
            Dashboard = app.Bean.extend({
                sync: sync,
                apiModule: 'Dashboards',
                module: 'Home',
                dashboardModule: module,
                maxColumns: (module === 'Home') ? 3 : 1,
                maxRowColumns: (module === 'Home') ? 3 : 2
            }),
            DashboardCollection = app.BeanCollection.extend({
                sync: sync,
                apiModule: 'Dashboards',
                module: 'Home',
                dashboardModule: module,
                model: Dashboard
            });
        if (options.meta && options.meta.method && options.meta.method === 'record' && !context.get("modelId")) {
            context.set("create", true);
        }
        var model = new Dashboard();
        model.set("view", view);
        if (context.get("modelId")) {
            model.set("id", context.get("modelId"), {silent: true});
        }
        context.set("model", model);
        context.set("collection", new DashboardCollection());
        app.view.Layout.prototype.initialize.call(this, options);
        this.model.on("setMode", function (mode) {
            if (mode === "edit" || mode === "create") {
                this.$(".dashboard").addClass("edit");
            } else {
                this.$(".dashboard").removeClass("edit");
            }
        }, this);
        this.initDashletPlugin();
        if (module === 'Home') {
            this.on("render", this.toggleSidebar, this);
            
            if (context.get("modelId")) {
                // save it as last visit
                app.user.setPreference('home-last-visit', context.get("modelId"));
            }
        }
    },
    initDashletPlugin: function () {
        if (app.plugins._get('Dashlet', 'view')) {
            return;
        }
        var sync = function (method, model, options) {
                options = app.data.parseOptionsForSync(method, model, options);
                var callbacks = app.data.getSyncCallbacks(method, model, options),
                    path = (this.dashboardModule === 'Home' || model.id) ? this.apiModule : this.apiModule + '/' + this.dashboardModule;
                app.api.records(method, path, model.attributes, options.params, callbacks);
            },
            Dashlet = app.Bean.extend({
                sync: sync,
                apiModule: 'Dashboards',
                module: 'Home'
            });


        app.plugins.register('Dashlet', 'view', {
            onAttach: function () {
                this.on("init", function () {
                    this.dashletConfig = app.metadata.getView(this.module, this.name);
                    this.dashModel = this.layout.context.get("model");

                    var settings = _.extend({}, this.meta),
                        viewName = 'main',
                        buildGrid = false;
                    delete settings.panels;
                    delete settings.type;
                    delete settings.action;
                    delete settings.dependencies;
                    this.settings = new Dashlet(settings);
                    if (settings.module) {
                        this.model = this.context.parent.get("model");
                    }
                    if (this.meta && this.meta.config) {
                        viewName = 'config';
                        this.createMode = true;
                        this.action = 'edit';
                        this.model = this.context.parent.get("model");
                        //needed to allow the record hbt to render our settings rather than the context model
                        this.dashModel.set(settings);
                        this.dashModel.set("componentType", (this instanceof app.view.Layout) ? "layout" : "view");

                        this.settings.on("change", function(model) {
                            this.dashModel.set(model.changed);
                        }, this);
                        this.model.isNotEmpty = true;

                        this.meta.panels = this.dashletConfig.panels;
                        var templateName = this.name + '.dashlet-config';
                        this.template = app.template.getView(templateName, this.module) ||
                            app.template.getView(templateName);
                        if (!this.template) {
                            this.template = app.template.getView('dashletconfiguration-edit') || app.template.empty;
                            var originalPlugins = this.plugins;
                            this.plugins = ['GridBuilder'];
                            app.plugins.attach(this, 'view');
                            this.plugins = _.union(this.plugins, originalPlugins);
                            buildGrid = true;
                        }
                    } else if (this.meta && this.meta.preview) {
                        viewName = 'preview';
                        this.settings.module = this.module;
                        var templateName = this.name + '.dashlet-preview';
                        this.template = app.template.getView(templateName, this.module) ||
                            app.template.getView(templateName) ||
                            this.template;
                    } else {
                        this.settings.module = this.module;
                    }
                    if (this.initDashlet && _.isFunction(this.initDashlet)) {
                        this.initDashlet(viewName);
                    }
                    if (buildGrid) {
                        this._buildGridsFromPanelsMetadata();
                    }
                });
            },
            /**
             * Build grid panel metadata based on panel span size
             */
            _buildGridsFromPanelsMetadata: function() {
                _.each(this.meta.panels, function (panel) {
                    // it is assumed that a field is an object but it can also be a string
                    // while working with the fields, might as well take the opportunity to check the user's ACLs for the field
                    _.each(panel.fields, function (field, index) {
                        if (_.isString(field)) {
                            panel.fields[index] = field = {name: field};
                        }
                    }, this);

                    // labels: visibility for the label
                    if (_.isUndefined(panel.labels)) {
                        panel.labels = true;
                    }

                    if (_.isFunction(this.getGridBuilder)) {
                        var options = {
                            fields:      panel.fields,
                            columns:     panel.columns,
                            labels:      panel.labels,
                            labelsOnTop: panel.labelsOnTop,
                            tabIndex:    0
                        },
                            gridResults = this.getGridBuilder(options).build();

                        panel.grid   = gridResults.grid;
                    }
                }, this);
            },
            onDetach: function() {
                this.settings.off();
                delete this.dashletConfig;
                delete this.dashModel;
            }
        });
    },
    loadData: function (options, setFields) {
        if (this.context.parent && !this.context.parent._dataFetched) {
            var parent = this.context.parent.get("modelId") ? this.context.parent.get("model") : this.context.parent.get("collection");

            parent.once("sync", function () {
                app.view.Layout.prototype.loadData.call(this, options, setFields);
            }, this);
        } else {
            app.view.Layout.prototype.loadData.call(this, options, setFields);
        }
    },
    toggleSidebar: function () {
        if (!this.toggled) {
            app.controller.context.trigger('toggleSidebar');
            this.toggled = true;
        }
    },

    /**
     * Places only components that include the Dashlet plugin and places them in the "main-pane" div of
     * the dashlet layout.
     * @param component {app.view.Component}
     * @private
     */
    _placeComponent: function (component) {
        var dashboardEl = this.$("[data-dashboard]"),
            css = this.context.get("create") ? " edit" : "";
        if (dashboardEl.length == 0) {
            dashboardEl = $("<div></div>").attr({
                'class': 'cols row-fluid'
            });
            this.$el.append(
                $("<div></div>").attr({
                    'class': 'dashboard main-pane' + css,
                    'data-dashboard': 'true'
                }).append(
                        dashboardEl
                    )
            );
        } else {
            dashboardEl = dashboardEl.children(".row-fluid");
        }
        dashboardEl.append(component.el);
    },
    dashboardLayouts: {
        'record': 'record-dashboard',
        'records': 'list-dashboard'
    },

    /**
     * If current context doesn't contain dashboard model id,
     * it will trigger set default dashboard to create default metadata
     */
    bindDataChange: function () {
        var modelId = this.context.get("modelId");
        if (!(modelId && this.context.get("create")) && this.collection) {
            this.collection.on("reset", this.setDefaultDashboard, this);
        }
    },

    /**
     * Build the default dashboard metadata only if dashboards are empty
     *
     * Default dashboard metadata are stored in the following layout metadata
     * listview - list-dashboard
     * recordview - record-dashboard
     */
    setDefaultDashboard: function() {
        if (this.disposed) {
            return;
        }
        var self = this;
        if (this.collection.models.length > 0) {
            var model = _.first(this.collection.models);
            if (this.context.parent) {
                //For other modules
                this.navigateLayout(model.id);
            } else {
                if (app.user.getPreference('home-last-visit')) {
                    model = _.findWhere(this.collection.models, {id: app.user.getPreference('home-last-visit')});
                }
                app.navigate(this.context, model);
            }
        } else {
            var layoutName = this.dashboardLayouts[this.context.parent ? this.context.parent.get("layout") : 'record'],
                _initDashboard = app.metadata.getLayout(this.model.dashboardModule, layoutName),
                params = {
                    silent: true,
                    //Don't show alerts for this request
                    showAlerts: false
                };

            if (this.context.parent) {
                params.success = function (model) {
                    self.navigateLayout(model.id);
                };
                params.error = function () {
                    self.navigateLayout("create");
                };
            } else {
                params.success = function (model) {
                    app.navigate(self.context, model);
                };
                params.error = function () {
                    var route = app.router.buildRoute(self.module, null, 'create');
                    app.router.navigate(route, {trigger: true});
                };
            }

            if (!_.isEmpty(_initDashboard) && !_.isEmpty(_initDashboard.metadata)) {
                this.model.set(_initDashboard, {silent: true});
                this.model.save({}, params);
            } else {
                params.error();
            }
        }
    },
    /**
     * For the RHS dashboard, this method loads entire dashboard component
     *
     * @param id {String} - dashboard id
     */
    navigateLayout:function (id) {
        var layout = this.layout;
        this.dispose();

        layout._addComponentsFromDef([
            {
                layout:{
                    name:'dashboard',
                    components:[
                        {
                            view: 'dashboard-headerpane'
                        },
                        {
                            layout: 'dashlet-main'
                        }
                    ]
                },
                context: _.extend({
                    module: 'Home',
                    forceNew: true
                }, (id === "create") ? {create: true} : (id !== "list") ? {modelId: id} : {})
            }
        ]);
        layout.removeComponent(0);
        layout.loadData({}, false);
        layout.render();
    },
    unbindData: function() {
        var model, collection;
        this.off("render", this.toggleSidebar, this);
        if (this.collection) {
            this.collection.off("reset", this.setDefaultDashboard, this);
        }
        if (this.context.parent) {
            model = this.context.parent.get("model");
            collection = this.context.parent.get("collection");

            if (model) {
                model.off("sync", null, this);
            }
            if (collection) {
                collection.off("sync", null, this);
            }
        }

        app.view.Layout.prototype.unbindData.call(this);
    },
    _dispose: function () {
        this.dashboardLayouts = null;
        app.view.Layout.prototype._dispose.call(this);
    }
})
