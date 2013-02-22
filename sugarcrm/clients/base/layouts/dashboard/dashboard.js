({
    toggled: false,
    className: 'row-fluid',
    initialize:function (options) {

        var context = options.context,
            sync = function(method, model, options) {
                options       = app.data.parseOptionsForSync(method, model, options);
                var callbacks = app.data.getSyncCallbacks(method, model, options);
                app.api.records(method, this.apiModule, model.attributes, options.params, callbacks);
            },
            Dashboard = app.Bean.extend({
                sync: sync,
                apiModule: 'Dashboards',
                module: 'Home'
            }),
            DashboardCollection = app.BeanCollection.extend({
                sync: sync,
                apiModule: 'Dashboards',
                module: 'Home',
                model: Dashboard
            });
        if(options.meta && options.meta.method && options.meta.method === 'record' && !context.get("modelId")) {
            context.set("create", true);
        }
        var model = new Dashboard();
        if(context.get("modelId")) {
            model.set("id", context.get("modelId"), {silent: true});
        }
        context.set("model", model);
        context.set("collection", new DashboardCollection());
        app.view.Layout.prototype.initialize.call(this, options);
        this.initDashletPlugin();
        if(!this.context.parent) {
            this.on("render", this.toggleSidebar);
        }
    },
    initDashletPlugin: function() {
        if(app.plugins._get('Dashlet', 'view')) return;
        app.plugins.register('Dashlet', 'view', {
            onAttach: function() {
                this.on("init", function() {
                    this.model.isNotEmpty = true;
                    var dashlet_context = this.context.get("dashlet"),
                        viewName = dashlet_context.viewName;
                    delete dashlet_context.viewName;
                    this.model.set(_.extend({
                        name: dashlet_context.name,
                        type: dashlet_context.type
                    }, dashlet_context));
                    if(viewName === "config") {
                        this.createMode = true;
                        this.action = 'edit';
                        this.layout.context.set("model", this.context.get("model"));
                        var templateName = this.name + '.dashlet-config';
                        this.template = app.template.getView(templateName, this.module) ||
                                        app.template.getView(templateName) ||
                                        app.template.getView('record') ||
                                        this.template;
                    } else if(viewName === "preview") {
                        this.layout.context.set("model", this.context.get("model"));
                        var templateName = this.name + '.dashlet-preview';
                        this.template = app.template.getView(templateName, this.module) ||
                                        app.template.getView(templateName) ||
                                        this.template;
                    }

                    if(this.initDashlet && _.isFunction(this.initDashlet)) {
                        this.initDashlet(viewName);
                    }
                });
            }
        });
    },
    toggleSidebar: function() {
        if(!this.toggled) {
            app.controller.context.trigger('toggleSidebar');
            this.toggled = true;
        }
    },
    _placeComponent: function(component) {
        var dashboardEl = this.$("#dashboard");
        if(dashboardEl.length == 0) {
            dashboardEl = $("<div></div>").attr({
                class: 'cols row-fluid'
            });
            this.$el.append(
                $("<div></div>").attr({
                    id : 'dashboard',
                    class: 'dashboard main-pane'
                }).append(
                    dashboardEl
                )
            );
        }
        dashboardEl.append(component.el);
    },
    bindDataChange: function() {
        var modelId = this.context.get("modelId");
        if(!(modelId && this.context.get("create")) && this.collection) {
            this.collection.on("reset", function() {
                if(this.collection.models.length > 0) {
                    var model = _.first(this.collection.models);
                    if(!this.context.parent) {
                        app.navigate(this.context, model);
                    } else {
                        //For other modules
                        this.context.set("model", model);
                        //this.context.unset("collection");
                        //this.context.set("module", "Home");
                        model.fetch();
                        this._addComponentsFromDef([{
                            layout: 'dashlet-main'
                        }]);
                        this.loadData();
                        this.render();
                    }
                } else {
                    if(!this.context.parent) {
                        var route = app.router.buildRoute(this.module, null, 'create');
                        app.router.navigate(route, {trigger: true});
                    }
                }
            }, this);
        }
    }
})
