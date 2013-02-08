({
    toggled: false,
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
        if(options.meta.method && options.meta.method === 'record' && !context.get("modelId")) {
            context.set("create", true);
        }
        var model = new Dashboard();
        if(context.get("modelId")) {
            model.set("id", context.get("modelId"), {silent: true});
        }
        context.set("model", model);
        context.set("collection", new DashboardCollection());
        app.view.Layout.prototype.initialize.call(this, options);

        this.on("render", this.toggleSidebar);
    },
    toggleSidebar: function() {
        if(!this.toggled) {
            app.controller.context.trigger('toggleSidebar');
            this.toggled = true;
        }
    },
    loadData: function(options) {
        this.context.loadData(options);
        /*
        if(this.model.get("id")) {
            this.model.fetch();
        } else if(!this.context.get("create")) {
            this.collection.fetch();
        }*/
    },
    bindDataChange: function() {
        var modelId = this.context.get("modelId");
        if(!(modelId && this.context.get("create")) && this.collection) {
            this.collection.on("reset", function() {
                if(this.collection.models.length > 0) {
                    var model = _.first(this.collection.models);
                    app.navigate(this.context, model);
                } else {
                    var route = app.router.buildRoute(this.module, null, 'create');
                    app.router.navigate(route, {trigger: true});
                }
            }, this);
        }
    }
})
