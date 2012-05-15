(function(app) {
    /**
     * Controller manages the loading and unloading of Views within the app. It extends from a Backbone.View.
     * @class Core.Controller
     * @singleton
     * @alias SUGAR.App.controller
     */
    var Controller = Backbone.View.extend({
        /**
         * Initialize our controller with a context object
         * @private
         * @method
         */
        initialize: function() {
            /**
             * The primary context state variable - the states associated with the focus of the View
             * @property {Object}
             */
            this.context = app.context.getContext();

            // Subscribe and publish events
            app.events.register(
                /**
                 * Start event. Fired when the application has
                 * finished loading its dependencies and should initialize
                 * everything.
                 *
                 * <pre><code>
                 * obj.on("app:start", callback);
                 * </pre></code>
                 * @event
                 */
                "app:start",
                this
            );

            // When the app has been synced, start the rest of the app flow.
            app.events.on("app:sync:complete", this.syncComplete);
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
            this.context.prepareData();
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

            // Render the rendered layout to the main element
            this.$('#content').html(this.layout.$el);

            // Fetch the data, the layout will be rendered when fetch completes
            this.context.loadData();
        },

        /**
         * Callback function once the app.sync() finishes. This should check if
         * the current user has authenticated or not and handle the redirection
         * if necessary.
         * @method
         */
        syncComplete: function() {
            app.router.start();
        }
    });

    /**
     * Should be auto initialized by the app.
     * @private
     */
    var module = {
        /**
         * Initializes this module when a new instance of App is created.
         *
         * @param {Object} instance The instance of the App
         * @param {Array} modules An optional list of modules to initialize
         * @method
         */
        initController: function(instance, modules) {
            if (modules && _.indexOf(modules, "controller") == -1) {
                return;
            }

            instance.controller = _.extend(module, instance.controller, new Controller({el: app.rootEl}));
        }
    };

    app.events.on("app:init", module.initController);
    app.augment("controller", module);
})(SUGAR.App);
