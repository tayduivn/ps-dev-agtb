(function(app) {

    /**
     * Base Layout class. Use {@link View.LayoutManager} to create instances of layouts.
     *
     * @class View.Layout
     * @alias SUGAR.App.layout.Layout
     * @extends View.View
     */
    app.view.Layout = app.view.View.extend({
        initialize: function() {
            _.bindAll(this, 'render', 'bindData');

            /**
             * The context is used to determine what the current focus is
             * (includes a model, collection, and module)
             * @cfg {Core.Context}
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
                    this.addComponent(app.view.createView({
                        context: context,
                        name: def.view,
                        module: module
                    }), def);
                }
                //Layouts can either by referenced by name or defined inline
                else if (def.layout) {
                    if (typeof def.layout == "string") {
                        this.addComponent(app.view.createLayout({
                            context: context,
                            name: def.layout,
                            module: module
                        }), def);
                    } else if (typeof def.layout == "object") {
                        //Inline definition of a sublayout
                        this.addComponent(app.view.createLayout({
                            context: context,
                            module: module,
                            meta: def.layout
                        }), def);
                    }
                }
            }, this);
        },

        /**
         * Add a view (or layout) to this layout.
         * @param {View.Layout/View.View} comp Componant to add
         * @param {Array} def Metadata definition
         */
        addComponent: function(comp, def) {
            this.components.push(comp);
            this._placeComponent(comp, def);
        },

        /**
         * Places a view's element on the page. This shoudl be overriden by any custom layout types.
         * @param {View.View} comp
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
         * @param {View.Layout/View.View/Number} comp Layout / View to remove
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

})(SUGAR.App);