(function(app) {

    /**
     * Base class for layouts.
     *
     * Use {@link View.ViewManager} to create instances of layouts.
     *
     * @class View.Layout
     * @alias SUGAR.App.view.Layout
     * @extends View.Component
     */
    app.view.Layout = app.view.Component.extend({

        className: "layout",

        events: Backbone.Events,

        /**
         * TODO docs (describe constructor options, see Component class for an example).
         *
         * @constructor
         * @param options
         */
        initialize: function(options) {
            app.view.Component.prototype.initialize.call(this, options);

            // TODO: Do we need this?
            //_.bindAll(this, 'render', 'bindData');

            this._components = []; // list of components

            if (!this.meta) return;

            /**
             * Reference to the parent layout instance.
             * @property {View.Layout}
             */
            this.layout = this.options.layout;

            /**
             * CSS class.
             *
             * CSS class which is specified as the `className` parameter
             * in `params` hash for {@link View.ViewManager#createLayout} method.
             *
             * By default the layout is rendered as `div` element with CSS class `"layout <layoutType>"`.
             * @cfg {String} className
             */
            this.$el.addClass(options.className || (this.meta.type ? this.meta.type : ""));

            _.each(this.meta.components, function(def) {
                var context = this.context;
                var module = this.module;
                // Switch context if necessary
                if (def.context) {
                    context = this.context.getChildContext(def.context);
                    context.prepare();
                    module = context.get("module");
                }

                if (def.view) {
                    var view = app.view.createView({
                        context: context,
                        name: def.view,
                        module: module,
                        layout: this
                    });
                    context.set({view:view});
                    this.addComponent(view, def);
                }
                // Layouts can either by referenced by name or defined inline
                else if (def.layout) {
                    if (_.isString(def.layout)) {
                        this.addComponent(app.view.createLayout({
                            context: context,
                            name: def.layout,
                            module: module,
                            layout: this
                        }), def);
                    } else if (_.isObject(def.layout)) {
                        //Inline definition of a sublayout
                        this.addComponent(app.view.createLayout({
                            context: context,
                            module: module,
                            meta: def.layout,
                            layout: this
                        }), def);
                    }
                }
                else {
                    app.logger.warn("Invalid layout definition:\n" + def.layout);
                }
            }, this);
        },

        /**
         * Adds a component to this layout.
         * @param {View.Layout/View.View} component Component (view or layout) to add
         * @param {Object} def Metadata definition
         */
        addComponent: function(component, def) {
            if (!component.layout) component.layout = this;
            this._components.push(component);
            this._placeComponent(component, def);
        },

        /**
         * Places layout component in the DOM.
         *
         * Default implementation just appends all the components to itself.
         * Override this method to support custom placement of components.
         *
         * @param {View.View/View.Layout} component View or layout component.
         * @protected
         */
        _placeComponent: function(component) {
            this.$el.append(component.el);
        },

        /**
         * Removes a component from this layout.

         * If component is an index, remove the component at that index. Otherwise see if component is in the array.
         * @param {View.Layout/View.View/Number} component The layout or view to remove.
         */
        removeComponent: function(component) {
            var i = _.isNumber(component) ? component : this._components.indexOf(component);

            if (i > -1) {
                var removed = this._components.splice(i, 1);
                removed[0].layout = null;
            }
        },

        /**
         * Renders all the components.
         */
        render: function() {
            if (this._components && this._components.length > 0) {
                //default layout will pass render container divs and pass down to all its views.
                _.each(this._components, function(component) {
                    component.render();
                }, this);
            }
            else {
                // This should never happen :)
                app.logger.warn("Can't render anything because the layout has no components: " + this.toString() + "\n" +
                    "Either supply metadata or override Layout.render method");
                // TODO: Revisit this. At least the message should be localized
                app.alert.show("no-layout", {
                    level: "error",
                    title: "Error",
                    messages: ["Oops! We are not able to render anything. Please try again later or contact the support"]
                });
            }
            return this;
        },

        /**
         * Gets a list of all fields used on this layout and its sub layouts/views.
         *
         * @param {String} module(optional) Module name.
         * @return {Array} The list of fields used by this layout.
         */
        getFieldNames: function(module) {
            var fields = [];
            module = module || this.module;
            _.each(this._components, function(component) {
                if (component.module == module) {
                    fields = _.union(fields, component.getFieldNames());
                }
            }, this);

            return fields;
        },

        /**
         * Gets a hash of fields that are currently displayed on this layout.
         *
         * The hash has field names as keys and field definitions as values.
         * @param {String} module(optional) Module name.
         * @return {Object} The currently displayed fields.
         */
        getFields: function(module) {
            var fields = {};
            _.each(this._components, function(component) {
                _.extend(fields, component.getFields(module));
            });
            return fields;
        },

        /**
         * Gets a string representation of this layout.
         * @return {String} String representation of this layout.
         */
        toString: function() {
            return "layout-" + (this.options.type || this.options.name) + "-" +
                app.view.Component.prototype.toString.call(this);
        }

    });

})(SUGAR.App);