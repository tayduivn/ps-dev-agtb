(function(app) {
    app.augment("layout", function() {
        var ucfirst = function(str) {
            if (_.isString(str)) {
                return str.charAt(0).toUpperCase() + str.substr(1);
            }
        };

        /**
         * Layout Manager is used to retrieve views and layouts based on metadata inputs.
         * @class LayoutManager
         * @alias SUGAR.App.layout
         * @singleton
         */
        var Layout = {
            init: function(args) {
                Handlebars.registerHelper('get_field_value', function(bean, field) {
                    return bean.get(field);
                });

                Handlebars.registerHelper('buildRoute', function(context, model, action, options) {
                    var id = model.id,
                        route;

                    options = options || {};

                    if (action == 'create') {
                        id = '';
                    }

                    route = app.router.buildRoute(context.get("module"), id, action, options);
                    return new Handlebars.SafeString(route);
                });

                Handlebars.registerHelper('getfieldvalue', function(bean, field) {
                    return bean.get(field);
                });

                Handlebars.registerHelper('eqEcho', function(v1, v2,rt,rf) {
                                  if(v1 == v2)return rt;
                                  if(rf)return rf;
                                  return "";
                });
                Handlebars.registerHelper("handleBarsLog", function(value) {
                                  console.log("*****Current Context*****");
                                  console.log(this);
                                  console.log("*****Current Value*****");
                                  console.log(value);
                                  console.log("***********************");

                                });
            },

            //All retreives of metadata should hit this function.
            //TODO: Probably refactor this function, it's quite large
            /**
             * Returns a Layout or View
             * @method
             * @param {Object} params Contains either view or layout to specify which type of
             * component you are retreiving.
             */
            get: function(params) {
                var meta = params.meta;
                var layoutClass = "Layout";
                var viewClass = "View";
                var ucType;

                if (!params.view && !params.layout)
                    return null;

                var context = params.context || app.controller.context;
                var module = params.module || context.get("module");

                //Ensure we have a module for the layout
                if (meta && !meta.module) {
                    meta.module = module;
                }

                var view = null;

                if (params.view) {
                    meta = meta || app.metadata.get({
                        type: "view",
                        module: module,
                        view: params.view
                    }) || {};
                    ucType = ucfirst(meta.view || params.type || params.view);

                    //Check if the view type has its own view subclass
                    if (meta && app.layout[ucType + "View"]) {
                        viewClass = ucType + "View";
                    }

                    if (meta && app.layout[ucType]) {
                        viewClass = ucType;
                    }

                    view = new app.layout[viewClass]({
                        context: params.context,
                        name: params.view,
                        meta: meta
                    });
                } else if (params.layout) {
                    meta = params.meta || app.metadata.get({
                        type: "layout",
                        module: module,
                        layout: params.layout
                    });

                    ucType = ucfirst(meta.type);

                    //Check if the layout type has its own layout subclass
                    if (meta && app.layout[ucType + "Layout"]) {
                        layoutClass = ucType + "Layout";
                    }

                    view = new app.layout[layoutClass]({
                        context: params.context,
                        name: params.layout,
                        module: module,
                        meta: meta
                    });
                }

                if (view) {
                    context.set({view: view});
                }

                return view;
            }
        };

        /**
         * Base View class. Use {@link LayoutManager} to create instances of views.
         *
         * @class Layout.View
         * @alias SUGAR.App.layout.View
         */
        Layout.View = Backbone.View.extend({
            initialize: function(options) {
                _.bindAll(this, 'render', 'bindData');

                /**
                 * The context is used to determine what the current focus is
                 * (includes a model, collection, and module)
                 * @cfg {Context}
                 */
                this.context = options.context || app.controller.context;

                /**
                 * Name of the View
                 * @cfg {String}
                 */
                this.name = options.name;

                /**
                 * Id of the View. Autogenerated if not specified.
                 * @cfg {String}
                 * @type {String}
                 */
                this.id = options.id || this.getID();

                /**
                 * Classname of the root div. Uses name if none is provided.
                 * @cfg {String} className
                 */
                this.$el.addClass("view " + (options.className || this.name));

                /**
                 * Template to render
                 * @cfg {Template}
                 */
                this.template = options.template || app.template.get(this.name, this.context.get("module"));

                /**
                 * Metadata
                 * @cfg {Object}
                 */
                this.meta = options.meta;
                this.sugarFields = {};
            },

            /**
             * Bind render to data
             * @method
             * @param {Object} data Model or collection to bind to
             */
            bindData: function(data) {
                data.on('reset', this.render);
            },

            /**
             * Bind this view to listen to the given context's event.
             * @param {Context} context
             */
            bind: function(context) {

            },
            
            /**
             * Renders the view onto the page. (should be overriden by subclasses instead of the formal render function if they need more advanced rendering or do not use a template)
             * @protected
             * @method
             */
            _render: function() {
                if (this.template) {
                    this.$el.html(this.template(this));
                }
            },
            
            /**
             * Renders the view onto the page. See Backbone.View
             * @return {Object} this
             */
            render: function() {
                //Bad templates can cause a JS error that we want to catch here
                try {
                    this._render();
                    //Render will create a placeholder for sugar fields. we now need to populate those fields
                    _.each(this.sugarFields, function(sf) {
                        sf.setElement(this.$el.find("span[sfuuid='" + sf.sfid + "']"));
                        sf.render();
                    }, this);
                } catch (e) {
                    app.logger.error("Runtime template error in " + this.name + ".\n" + e.message);
                }
                
                return this;
            },

            /**
             * Extracts the fields from the metadata for directly related views/panels
             * TODO: Possibly refactor
             * @return {Array} List of fields used on this view
             */
            getFields: function() {
                var fields = [];
                if (this.meta && this.meta.panels) {
                    _.each(this.meta.panels, function(panel) {
                        fields = fields.concat(_.pluck(panel.fields, 'name'));
                    });
                }

                return _.filter(_.uniq(fields), function(value) {
                    return value;
                });
            },
            
            /**
             * Returns the html id of this view's el. Will create one if one doesn't exist.
             * @return {String} id of this view.
             */
            getID: function() {
                return this.id || this.context.get("module") + "_" + this.options.name;
            }
        });

        /**
         * View that displays a list of models pulled from the context's collection.
         * @class Layout.ListView
         * @alias SUGAR.App.layout.ListView
         * @extends Layout.View
         */
        Layout.ListView = Layout.View.extend({
            bind: function(context) {
                var collection = context.get("collection");
                _.each(collection.models, function(model) {
                    var tr = this.$el.find('tr[name="' + model.module + '_' + model.get("id") + '"]');
                    _.each(model.attributes, function(value, field) {
                        var el = tr.find('input[name="' + field + '"],span[name="' + field + '"]');
                        if (el.length > 0) {
                            //Bind input to the model
                            el.on("change", function(ev) {
                                model.set(field, el.val());
                            });
                            //And bind the model to the input
                            model.on("change:" + field, function(model, value) {
                                if (el[0].tagName.toLowerCase() == "input")
                                    el.val(value); else
                                    el.html(value);
                            });
                        }
                    }, this);
                }, this);
            }
        });

        /**
         * Base Layout class. Use {@link LayoutManager} to create instances of layouts.
         *
         * @class Layout.Layout
         * @alias SUGAR.App.layout.Layout
         * @extends Layout.View
         */
        Layout.Layout = Layout.View.extend({
            initialize: function() {
                _.bindAll(this, 'render', 'bindData');

                /**
                 * The context is used to determine what the current focus is
                 * (includes a model, collection, and module)
                 * @cfg {Context}
                 */
                this.context = this.options.context || app.controller.context;

                /**
                 * Module
                 * @cfg {String}
                 */
                this.module = this.options.module || this.context.module;

                /**
                 * Metadata
                 * @cfg {Object}
                 */
                this.meta = this.options.meta;

                /**
                 * Components array
                 * @cfg {Array}
                 * @private
                 */
                this.components = [];

                /**
                 * Classname of the View
                 * @cfg {String} className
                 */
                this.$el.addClass("layout " + (this.options.className || this.meta.type));

                _.each(this.meta.components, function(def) {
                    var context = def.context ? this.context.getRelatedContext(def.context) : this.context,
                        module = def.module || context.get("module");

                    //If the context wasn't specified in the def, use the parent layouts module
                    // (even if that isn't the module of the current context)
                    if (!def.context)
                        module = this.module;

                    if (def.view) {
                        this.addComponent(app.layout.get({
                            context: context,
                            view: def.view,
                            module: module
                        }), def);
                    }
                    //Layouts can either by referenced by name or defined inline
                    else if (def.layout) {
                        if (typeof def.layout == "string") {
                            this.addComponent(app.layout.get({
                                context: context,
                                layout: def.layout,
                                module: module
                            }), def);
                        } else if (typeof def.layout == "object") {
                            //Inline definition of a sublayout
                            this.addComponent(app.layout.get({
                                context: context,
                                module: module,
                                layout: true,
                                meta: def.layout
                            }), def);
                        }
                    }
                }, this);
            },
            
            /**
             * Add a view (or layout) to this layout.
             * @param {Layout/View} comp Componant to add
             * @param {array} def Metadata definition
             */
            addComponent: function(comp, def) {
                this.components.push(comp);
                this._placeComponent(comp, def);
            },

            /**
             * Places a view's element on the page. This shoudl be overriden by any custom layout types.
             * @param {View} comp
             * @protected
             * @method
             */
            //Default layout just appends all the components to itself
            _placeComponent: function(comp) {
                this.$el.append(comp.el);
            },

            /**
             * Removes the given view / layout from this layout.
             * If comp is an index, remove the component at that index. Otherwise see if comp is in the array.
             * @param {Layout/View/Number} comp Layout / View to remove
             * @method
             */
            removeComponent: function(comp) {
                var i = typeof comp == "number" ? comp : this.components.indexOf(comp);

                if (i > -1) {
                    this.components.splice(i, 1);
                }
            },

            /**
             * Renders all the components
             * @method
             */
            render: function() {
                //default layout will pass render container divs and pass down to all its views.
                _.each(this.components, function(comp) {
                    comp.render();
                }, this);
            },

            /**
             * Used to get a list of all fields used on this layout and its sub layouts/views
             *
             * @method
             * @return {Array} list of fields used by this layout.
             */
            getFields: function() {
                var fields = [];
                _.each(this.components, function(view) {
                    fields = _.union(fields, view.getFields());
                });

                return fields;
            }
        });

        /**
         * Layout that places views in a table with each view in its own column
         * @class Layout.ColumnsLayout
         * @alias SUGAR.App.layout.ColumnsLayout
         * @extends Layout.Layout
         */
        Layout.ColumnsLayout = Layout.Layout.extend({
            //column layout uses a table for columns and prevent wrapping
            /**
             * Add a view (or layout) to this layout.
             * @param {Layout/View} comp Componant to add
             */
            _placeComponent: function(comp) {
                if (!this.$el.children()[0]) {
                    this.$el.append("<table><tbody><tr></tr></tbody></table>");
                }
                //Create a new td and add the layout to it
                $().add("<td></td>").append(comp.el).appendTo(this.$el.find("tr")[0]);
            }
        });

        /**
         * Layout that places components using bootstrap fluid layout divs
         * @class Layout.FluidLayout
         * @extends Layout.Layout
         */
        Layout.FluidLayout = Layout.Layout.extend({
            /**
             * Places a view's element on the page. This shoudl be overriden by any custom layout types.
             * @param {View} comp
             * @protected
             * @method
             */
            _placeComponent: function(comp, def) {
                var size = def.size || 4;
                if (!this.$el.children()[0]) {
                    this.$el.addClass("container-fluid").append('<div class="row-fluid"></div>');
                }

                //Create a new td and add the layout to it
                $().add("<div></div>").addClass("span" + size).append(comp.el).appendTo(this.$el.find("div.row-fluid")[0]);
            }
        });

        return Layout;
    }());
})(SUGAR.App);