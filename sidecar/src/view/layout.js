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

            /**
             * CSS class.
             * @cfg {String} className
             */
            this.$el.addClass("layout " + (options.className || this.meta.type));

            _.each(this.meta.components, function(def) {
                var context = this.context;
                var module = this.module;
                // Switch context if necessary
                if (def.context) {

                    context = this.context.getChildContext(def.context);
                    if (def.context.link) {
                        context.prepareRelatedData();
                        module = context.get('collection').module;
                    } else {
                        context.prepareData();
                        module = def.context.module || this.module;
                    }
                }

                if (def.view) {
                    var view = app.view.createView({
                        context: context,
                        name: def.view,
                        module: module
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
                            module: module
                        }), def);
                    } else if (_.isObject(def.layout)) {
                        //Inline definition of a sublayout
                        this.addComponent(app.view.createLayout({
                            context: context,
                            module: module,
                            meta: def.layout
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
                this._components.splice(i, 1);
            }
        },

        /**
         * Renders all the components.
         */
        render: function() {
            //default layout will pass render container divs and pass down to all its views.
            _.each(this._components, function(component) {
                component.render();
            }, this);
        },

        /**
         * Gets a list of all fields used on this layout and its sub layouts/views.
         *
         * @return {Array} The list of fields used by this layout.
         */
        getFields: function() {
            // TODO: Fix this method:
            // This method has a bug: it doesn't check for module, it collects fields from its views regadless of module
            var fields = [];
            var self = this;
            _.each(this._components, function(view) {
                if (view.context.get('module') == self.context.get('module')) {
                    fields = _.union(fields, view.getFields());
                }
            });

            return fields;
        }
    });

})(SUGAR.App);