(function (app) {
    app.augment("layout", function () {
        var Layout = {

            init:function (args) {

            },

            //All retreives of metadata should hit this function.
            get:function (params) {
                if ((!params.context && !params.module) || (!params.view && !params.layout))
                    return null;

                var module = params.module || params.context.module;
                if (params.view) {
                    return new app.layout.View({
                        context: params.context,
                        meta : params.meta || app.metadata.get({
                            type: "view",
                            module: module,
                            view: params.view
                        })
                    });
                } else if (params.layout) {
                    return new app.layout.Layout({
                        context: params.context,
                        meta : params.meta || app.metadata.get({
                            type: "layout",
                            module: module,
                            layout: params.layout
                        })
                    });
                }

                return null;
            }
        };
        Layout.View = Backbone.View.extend({
            initialize:function () {
                //The context is used to determine what the current focus is
                // (includes a model, collection, and module)
                this.context = this.options.context || app.context;
            },
            render:function () {

            }
        });
        Layout.Layout = Layout.View.extend({
            initialize:function () {
                //The context is used to determine what the current focus is
                // (includes a model, collection, and module)
                this.context = this.options.context || app.context;
                this.module = this.context.module;
                this.components = [];
                _.each(this.options.def.components, function (def) {
                    var context = def.context ? this.context.getRelatedContext(def.context) : this.context;
                    if (def.view) {
                        this.components.push(app.layout.get({
                            context:context,
                            view:def.view,
                            module:context.module
                        }));
                    }
                    //Layouts can either by referenced by name or defined inline
                    else if (def.layout) {
                        if (typeof def.layout == "string") {
                            this.components.push(app.layout.get({
                                context:context,
                                layout:def.layout,
                                module:context.module
                            }));
                        }
                        else if(typeof def.layout == "object") {
                            //Inline definition of a sublayout
                            this.components.push(app.layout.get({
                                context:context,
                                module:context.module,
                                layout:true,
                                meta: def.layout
                            }));
                        }
                    }
                });
            },
            render:function () {
                //default layout will pass render container divs and pass down to all its views.
            }
        });

        return Layout;
    }());
})(SUGAR.App);