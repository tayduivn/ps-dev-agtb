(function(app) {
    /**
     * Controller manages the loading and unloading of layouts within the app.
     *
     * @class Core.Controller
     * @singleton
     * @alias SUGAR.App.controller
     */
    var Controller = Backbone.View.extend({
        /**
         * Initializes this controller.
         * @private
         * @constructor
         * @ignore
         */
        initialize: function(options) {
            /**
             * The primary context of the app.
             * This context is associated with the root layout.
             * @property {Core.Context}
             */
            this.context = app.context.getContext();

            app.events.on("app:sync:complete", function() {
                app.router.start();
            });
        },

        /**
         * Loads a view (layout).
         *
         * This method is called by the router when the route is changed.
         *
         * @param {Object} params Options that determine the current context and the view to load.

         * - id: ID of the record to load (optional)
         * - module: module name
         * - layout: Name of the layout to .oad
         */
        loadView: function(params) {
            this.layout = null;

            // Reset context and initialize it with new params
            this.context.clear({silent: true});
            this.context.set(params);

            // Prepare model and collection
            this.context.prepare();
            // Create an instance of the layout and bind it to the data instance
            this.layout = app.view.createLayout({
                name: params.layout,
                module: params.module,
                context: this.context
            });

            //A context needs to have a primary layout to render to the page
            this.context.set({layout:this.layout});

            // Render the layout with empty data
            if (this.layout) {
                this.layout.render();
            }

            app.trigger("app:view:change", params.layout);

            // Render the layout to the main element
            app.$contentEl.html(this.layout.$el);

            // Fetch the data, the layout will be rendered when fetch completes
            this.context.loadData();
        },

        /**
         * Creates, renders, and registers within the app additional components.
         */
        loadAdditionalComponents: function(components) {
            // Unload components that may be loaded previously
            _.each(app.additionalComponents, function(component) {
                if (component) {
                    component.remove();
                    // TODO: Call dispose once it's implemented
                    //component.dispose();
                }
            });

            app.additionalComponents = {};
            _.each(components, function(component, name) {
                if (component.target) {
                    app.additionalComponents[name] = app.view.createView({
                        name: name,
                        context: this.context,
                        el: this.$(component.target)
                    }).render();
                }
            });
        }
    });

    app.augment("controller", new Controller(), false);

    app.events.on("app:init", function(app) {
        this.setElement(app.$rootEl);
    }, app.controller).on("app:start", function(app) {
        this.loadAdditionalComponents(app.config.additionalComponents);
    }, app.controller);

})(SUGAR.App);
