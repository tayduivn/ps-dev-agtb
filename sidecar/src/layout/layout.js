(function (app) {
    app.augment("layout", function () {
        var ucfirst = function(str) {
            if (typeof(str) == "string")
                return str.charAt(0).toUpperCase() + str.substr(1);
        }
        /**
         * App.Layout
         */
        var Layout = {
            init:function (args) {
                //Register Handlebars helpers
                Handlebars.registerHelper('sugar_field', function(context, view, bean) {
                    var ftype, sf;
                    //If bean was not specified, the third parameter will be a hash
                    if (!bean || !bean.fields)
                        bean = context.get("model");
                    if (!bean.fields[this.name] || !bean.fields[this.name].type)
                    {
                        //If the field doesn't exist for this bean type, skip it
                        app.logger.error("Sugar Field: Unknown field " + this.name + " for " + context.get("module") + ".");
                        return "";
                    }
                    ftype = bean.fields[this.name].type;
                    sf = app.sugarFieldManager.getField(ftype, view);
                    if (sf.error)
                        return sf.error;
                    this.value = bean.get(this.name);
                    this.view = view;
                    this.context = context;
                    try {
                        return new Handlebars.SafeString(sf.templateC(this));
                    } catch(e) {
                        app.logger.error("Sugar Field: Unable to execute template for field " + ftype + " on view " + this.name + ".\n" + e.message);
                    }

                });
            },

            //All retreives of metadata should hit this function.
            /**
             *
             * @param Object params should contain either view or layout to specify which type of
             * component you are retreiving.
             */
            get:function (params) {
                var meta = params.meta;
                var layoutClass = "Layout";
                var viewClass = "View";
                var ucType;

                if (!params.view && !params.layout)
                    return null;

                var context = params.context || app.controller.context;
                var module = params.module || context.get("module");
                //Ensure we have a module for the layout
                if (meta && !meta.module){
                    meta.module = module;
                }
                if (params.view) {
                    meta = meta || app.metadata.get({
                        type: "view",
                        module: module,
                        view: params.view
                    });
                    ucType = ucfirst(meta.view || params.type || params.view);
                    //Check if the view type has its own view subclass
                    if (meta && app.layout[ucType + "View"])
                        viewClass = ucType+ "View";

                    if (meta && app.layout[ucType])
                        viewClass = ucType;

                    return new app.layout[viewClass]({
                        context: params.context,
                        name : params.view,
                        meta : meta
                    });
                } else if (params.layout) {
                    meta = params.meta || app.metadata.get({
                        type: "layout",
                        module: module,
                        layout: params.layout
                    });
                    ucType = ucfirst(meta.type);
                    //Check if the layout type has its own layout subclass
                    if (meta && app.layout[ucType + "Layout"])
                        layoutClass = ucType + "Layout";
                    return new app.layout[layoutClass]({
                        context: params.context,
                        name : params.layout,
                        module: module,
                        meta : meta
                    });
                }

                return null;
            }
        };

        Layout.View = Backbone.View.extend({
            initialize:function (options) {
                //The context is used to determine what the current focus is
                // (includes a model, collection, and module)
                this.context = options.context || app.controller.context;
                this.name = options.name;
                //Create a unique ID for this view
                this.id = options.id || this.getID();
                this.$el.addClass("view " + (options.className || this.name));
                this.template = options.template || app.template.get(this.name, this.context.get("module"));
                this.meta = options.meta;
                //Bind will cause the view to automatically try to link form elements to attributes on the model
                this.autoBind = options.bind || true;

            },
            _render: function() {
                if (this.template)
                    this.$el.html(this.template(this));
            },
            render:function () {
                //Bad templates can cause a JS error that we want to catch here
                try {
                    this._render();
                    if (this.autoBind && this.context && this.context.get("model"))
                    {
                        this.bind(this.context);
                    } else {
                        console.log("not binding");
                    }
                } catch(e) {
                    app.logger.error("Runtime template error in " + this.name + ".\n" + e.message);
                }

            },
            bind : function(context) {
                var model = context.get("model");
                _.each(model.attributes, function(value, field) {
                    var el = this.$el.find('input[name="' + field + '"],span[name="' + field + '"]');
                    if (el.length > 0){
                        //Bind input to the model
                        el.on("change", function(ev){
                            model.set(field, el.val());
                        });
                        //And bind the model to the input
                        model.on("change:" + field, function(model, value){
                            if (el[0].tagName.toLowerCase() == "input")
                                el.val(value);
                            else
                                el.html(value);
                        });
                    }
                }, this)
            },
            getID : function() {
                if (this.id)
                    return this.id;

                return this.context.get("module") + "_" + this.options.name;
            }
        });
        Layout.EditView = Layout.View.extend({
            _render:function () {
                if (this.template)
                    this.$el.html(
                        this.template(this) +
                        "<br/>This is a custom view"
                    );
            }
        });
        Layout.ListView = Layout.View.extend({
            bind : function(context) {
                var collection = context.get("collection");
                _.each(collection.models, function(model) {
                    var tr = this.$el.find('tr[name="' + model.beanType + '_' + model.get("id") + '"]');
                    _.each(model.attributes, function(value, field) {
                        var el = tr.find('input[name="' + field + '"],span[name="' + field + '"]');
                        if (el.length > 0){
                            //Bind input to the model
                            el.on("change", function(ev){
                                model.set(field, el.val());
                            });
                            //And bind the model to the input
                            model.on("change:" + field, function(model, value){
                                console.log(el);
                                if (el[0].tagName.toLowerCase() == "input")
                                    el.val(value);
                                else
                                    el.html(value);
                            });
                        }
                    }, this)
                }, this)
            }
        })
        Layout.Layout = Layout.View.extend({
            initialize:function () {
                //The context is used to determine what the current focus is
                // (includes a model, collection, and module)
                this.context = this.options.context || app.context.getContext();
                this.module = this.options.module || this.context.module;
                this.meta = this.options.meta;
                this.components = [];
                this.$el.addClass("layout " + (this.options.className || this.meta.type));

                _.each(this.meta.components, function (def) {
                    var context = def.context ? this.context.getRelatedContext(def.context) : this.context;
                    var module = def.module || context.get("module") || this.module;
                    if (def.view) {
                        this.addComponent(app.layout.get({
                            context:context,
                            view:def.view,
                            module:module
                        }), def);
                    }
                    //Layouts can either by referenced by name or defined inline
                    else if (def.layout) {
                        if (typeof def.layout == "string") {
                            this.addComponent(app.layout.get({
                                context:context,
                                layout:def.layout,
                                module:module
                            }), def);
                        }
                        else if(typeof def.layout == "object") {
                            //Inline definition of a sublayout
                            this.addComponent(app.layout.get({
                                context:context,
                                module:module,
                                layout:true,
                                meta: def.layout
                            }), def);
                        }
                    }
                }, this);
            },
            addComponent : function(comp, def) {
                this.components.push(comp);
                this._placeComponent(comp, def);
            },
            //Default layout just appends all the components to itself
            _placeComponent: function(comp) {
                this.$el.append(comp.el);
            },
            removeComponent : function(comp) {
                //If comp is an index, remove the component at that index. Otherwise see if comp is in the array
                var i = typeof comp == "number" ? comp : this.components.indexOf(comp);
                if (i > -1)
                    this.components.splice(i,1);
            },
            render:function () {
                //default layout will pass render container divs and pass down to all its views.
                _.each(this.components, function(comp){
                    comp.render();
                }, this);
            }
        });

        Layout.ColumnsLayout = Layout.Layout.extend({
            //column layout uses a table for columns and prevent wrapping
            _placeComponent: function(comp) {
                if(!this.$el.children()[0]){
                    this.$el.append("<table><tbody><tr></tr></tbody></table>");
                }
                //Create a new td and add the layout to it
                $().add("<td></td>").append(comp.el).appendTo(this.$el.find("tr")[0]);
            }
        });

        /**
         * @class FluidLayout Layout that places components using bootstrap fluid layout divs
         * @extend App.Layout.Layout
         */
        Layout.FluidLayout = Layout.Layout.extend({
            _placeComponent: function(comp, def) {
                var size = def.size || 4;
                if(!this.$el.children()[0]){
                    this.$el.addClass("container-fluid").append('<div class="row-fluid"></div>');
                }

                //Create a new td and add the layout to it
                $().add("<div></div>").addClass("span" + size).append(comp.el).appendTo(this.$el.find("div.row-fluid")[0]);
            }
        });

        return Layout;
    }());
})(SUGAR.App);