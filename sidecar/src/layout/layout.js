(function (app) {
    app.augment("layout", function () {
        var Layout = {
            init:function (args) {
                var fieldCache = {};
                //Register Handlebars helpers
                Handlebars.registerHelper('sugar_field', function(context, view) {
                    var key = context.module + "_" + view + "_" + this.name;
                    if (!fieldCache[key]){
                        var ftype = app.metadata.get({type:"vardef",module:context.get("module")}).fields[this.name].type;
                        var t = app.sugarFieldManager.getField(ftype, view);
                        if (t.error)
                            return t.error;
                        fieldCache[key] = Handlebars.compile(t.template);
                    }
                    this.value = context.get("model").get(this.name);
                    return new Handlebars.SafeString(fieldCache[key](this));
                });

            },

            //All retreives of metadata should hit this function.
            get:function (params) {
                if ((!params.context && !params.module) || (!params.view && !params.layout))
                    return null;

                var module = params.module || params.context.get("module");
                if (params.view) {
                    return new app.layout.View({
                        context: params.context,
                        name : params.view,
                        meta : params.meta || app.metadata.get({
                            type: "view",
                            module: module,
                            view: params.view
                        })
                    });
                } else if (params.layout) {
                    return new app.layout.Layout({
                        context: params.context,
                        name : params.layout,
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
            initialize:function (options) {
                //The context is used to determine what the current focus is
                // (includes a model, collection, and module)
                this.context = options.context || app.context.getContext();
                this.name = options.name;
                //Create a unique ID for this view
                this.id = options.id || this.getID();
                this.className = options.className || this.name;
                this.template = options.template || app.template.get(this.name, this.context.get("module"));
                this.meta = options.meta;
            },
            render:function () {
                if (this.template)
                    this.$el.html(this.template(this));
            },
            getID : function() {
                if (this.id)
                    return this.id;

                return this.context.module + "_" + this.options.name;
            }
        });
        Layout.Layout = Layout.View.extend({
            initialize:function () {
                //The context is used to determine what the current focus is
                // (includes a model, collection, and module)
                this.context = this.options.context || app.context.getContext();
                this.module = this.context.module;
                this.components = [];
                _.each(this.options.meta.components, function (def) {
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
                }, this);
            },
            render:function () {
                //default layout will pass render container divs and pass down to all its views.
            }
        });

        return Layout;
    }());
})(SUGAR.App);