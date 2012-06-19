(function(app) {

    var NomadController = app.Controller.extend({

        initialize: function() {

            NomadController.__super__.initialize.apply(this,arguments);
            this.layoutHash = [];
            this.dataHash = {};

        },

        loadView: function(params) {

            var prevLayout = this.layout;
            if (prevLayout) {

                _.find(this.dataHash, function(cachedData) {
                    var flag = cachedData.cid === prevLayout.cid;
                    if (flag) {
                        cachedData.scrollTop = prevLayout.$el.find(".list-container").get(0).scrollTop;
                    }
                    return flag;
                });

            }

            var prevAction = prevLayout ? prevLayout.context.get("action") : null;

            if (prevLayout) {
                if (this.checkIsDisableCache(prevLayout.options.name)) {
                    prevLayout.dispose();
                } else {
                    prevLayout.detach();
                }
            }

            // Reset context and initialize it with new params
            this.context.clear({silent: true});
            this.context.set(params);

            // Prepare model and collection
            this.context.prepare();

            var currentLayout = this.getLayoutFromHash();

            var isNewLayout = !currentLayout;

            currentLayout = currentLayout || app.view.createLayout({
                name: params.layout,
                module: params.module,
                context: this.context
            });

            //A context needs to have a primary layout to render to the page
            this.context.set("layout", currentLayout);

            var cachedData = this.dataHash[(params.module || params.link).toLowerCase()];

            var data = cachedData ? cachedData.collection : null;

            if (data) {

                if (params.modelId) {

                    var model = data.get(params.modelId);
                    var collection = app.data.createBeanCollection([params.module, [model]]);

                    currentLayout.model = model;
                    currentLayout.collection = collection;

                    _.each(currentLayout._components,function(component){
                        component.model = model;
                        component.collection = collection;
                    });

                    this.context.set({'model': model});
                    this.context.set({'collection': collection});

                    currentLayout.render();
                    currentLayout.loadData();

                } else if (cachedData.cid === currentLayout.cid) {

                    this.context.set({'collection': data}, {silent: true});

                    if (prevAction === "create") {
                        currentLayout.loadData();
                    }

                } else {

                    if (!params.create) {
                        this.context.set({'collection': currentLayout.collection}, {silent: true});
                    }

                    if (isNewLayout) {
                        currentLayout.render();
                    }
                    currentLayout.loadData();
                }

            } else {

                // Render the layout with empty data
                if (isNewLayout) {
                    currentLayout.render();
                }
                currentLayout.loadData();
            }

            if (!this.checkIsDisableCache(params.layout)) {

                this.addLayoutToHash(currentLayout);

                if (!params.modelId && params["action"] !== "edit" && params["create"] !== true) {
                    this.dataHash[(params.module || params.link).toLowerCase()] = {
                        collection: this.context.get("collection"),
                        cid: currentLayout.cid
                    };
                }
            }

            // Render the layout to the main element
            app.$contentEl.html(currentLayout.$el);

            if(cachedData && cachedData.cid === currentLayout.cid){
                currentLayout.$el.find(".list-container").get(0).scrollTop = cachedData.scrollTop;
            }

            this.layout = currentLayout;

            app.trigger("app:view:change", params.layout, params);

        },

        checkIsDisableCache:function(layoutName){

            return !!_.find(app.config.disableLayoutCache,function(name){
                return layoutName === name;
            });
        },

        addLayoutToHash:function(layout){

            var o = _.find(this.layoutHash, function(o) {
                return o.url === Backbone.history.getHash();
            });

            if (!o) {
                if (this.layoutHash.length > app.config.layoutCacheSize) {
                    var l = this.layoutHash.shift().layout;
                    delete this.dataHash[(l.module).toLowerCase()];
                    l.dispose();
                }
                this.layoutHash.push({
                    url: Backbone.history.getHash(),
                    layout: layout
                });
            }

        },

        getLayoutFromHash:function(){

            var o = _.find(this.layoutHash, function(o) {
                return o.url === Backbone.history.getHash();
            });

            return o ? o.layout : null;

        }
    });

    app.augment("NomadController", NomadController, false);

})(SUGAR.App);