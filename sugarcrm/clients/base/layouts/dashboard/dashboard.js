/**
 * @class app.view.layouts.DashboardLayout
 *
 * The outer layout of the dashboard. This layout contains the header view and wraps the daslet-main layout. The layouts for each dashboard are stored in the dashboard endpoint (rest/v10/Dashboards/{id})
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
                maxRowColumns: (module === 'Home') ? 3 : 2,
                dashboardLayout: this
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
                this.$("#dashboard").addClass("edit");
            } else {
                this.$("#dashboard").removeClass("edit");
            }
        }, this);
        this.initDashletPlugin();
        if (module === 'Home') {
            this.on("render", this.toggleSidebar);
            
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
                if (method === 'read') {
                    options.params.view = view;
                }
                app.api.records(method, path, model.attributes, options.params, callbacks);
            },
            Dashlet = app.Bean.extend({
                sync:sync,
                apiModule:'Dashboards',
                module:'Home'
            });


        app.plugins.register('Dashlet', 'view', {
            onAttach:function () {
                this.on("init", function () {
                    this.dashletConfig = app.metadata.getView(this.module, this.name);
                    this.dashModel = this.layout.context.get("model");
                    var settings = _.extend({}, this.meta);
                    delete settings.panels;
                    delete settings.type;
                    delete settings.action;
                    this.settings = new Dashlet(settings);//
                    if (this.meta && this.meta.config) {
                        this.createMode = true;
                        this.action = 'edit';
                        //needed to allow the record hbt to render our settings rather than the context model
                        this.model = this.dashModel;
                        this.model.set(settings);
                        this.model.set("componentType", (this instanceof app.view.Layout) ? "layout" : "view");
                        this.model.isNotEmpty = true;
                        this.meta.panels = this.dashletConfig.panels;
                        var templateName = this.name + '.dashlet-config';
                        this.template = app.template.getView(templateName, this.module) ||
                            app.template.getView(templateName) ||
                            app.template.getView('record') ||
                            this.template;
                    } else if (this.meta && this.meta.preview) {
                        var templateName = this.name + '.dashlet-preview';
                        this.template = app.template.getView(templateName, this.module) ||
                            app.template.getView(templateName) ||
                            this.template;
                    }
                    if (this.initDashlet && _.isFunction(this.initDashlet)) {
                        this.initDashlet(this.name);
                    }
                });
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
     * @param component
     * @private
     */
    _placeComponent: function (component) {
        var dashboardEl = this.$(".dashboard"),
            css = this.context.get("create") ? " edit" : "";
        if (dashboardEl.length == 0) {
            dashboardEl = $("<div></div>").attr({
                'class': 'cols row-fluid'
            });
            this.$el.append(
                $("<div></div>").attr({
                    'class' : 'dashboard main-pane' + css
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
    bindDataChange: function () {
        var modelId = this.context.get("modelId"),
            self = this;
        if (!(modelId && this.context.get("create")) && this.collection) {
            this.collection.on("reset", function () {
                if (this.disposed) {
                    return;
                }

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
                        this.model.set(_initDashboard);
                        this.model.save({}, params);
                    } else {
                        params.error();
                    }
                }
            }, this);
        }
    },
    navigateLayout: function (id) {
        var layout = this.layout;
        this.dispose();

        layout._addComponentsFromDef([
            {
                layout: {
                    name: 'dashboard',
                    components: [
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
        if (this.collection) {
            this.collection.model.prototype.dashboardLayout = null;
        }
        this.model.parentModel = null;
        this.model.parentCollection = null;
        this.dashboardLayouts = null;
        app.view.Layout.prototype._dispose.call(this);
    }
})
