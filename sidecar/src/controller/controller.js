(function(app) {

    /**
     * Controller extends from a BackboneView
     * It requires the app to be initialized for the controller init to work
     *
     * @class Controller
     * @singleton
     */
    var Controller = Backbone.View.extend({
        /**
         * Initialize our controller with a context object
         * @private
         * @method
         */
        initialize: function() {
            this.context = app.context.getContext();

            // Subscribe and publish events

            /**
             * Fully qualified event name "app:start". Start event. Fired when the application has
             * finished loading its dependencies and should initialize
             * everything.
             *
             * <pre><code>
             * obj.bind("app:start", callback);
             * </pre></code>
             * @event start
             */
            app.events.register("app:start", this);

            // When the app has been synced, start the rest of the app flow.
            app.events.on("app:sync:complete", this.syncComplete);
        },

        /**
         * This is the main entry point from which the router tells the controller
         * what layout to load.
         *
         * @method
         * @param {Object} params Options to set the global context and the current layout
         *  @option {String} id Current Id of the global context
         *  @option {String} module Current module
         *  @option {String} layout Name of the current layout
         */
        loadView: function(params) {
            this.layout = null;

            this.context.init(params);
            this.layout = this.getLayout(params);

            this.context.getData(this);
            this.context.set({layoutObj: this.layout});

            // Render the rendered layout to the main element
            this.$el.html(this.layout.$el);

            // Render the layout
            this.layout.render();
        },

        /**
         * Returns a layout from the layout manager
         *
         * @private
         * @method
         * @param {Object} opts
         *  @option layout Layout to load
         *  @option module Current module
         * @return {Object} obj Layout obj
         */
        getLayout: function(opts) {
            return SUGAR.App.layout.get({
                layout: opts.layout,
                module: opts.module
            });
        },

        /**
         * Callback function once the app.sync() finishes.
         * @method
         */
        syncComplete: function() {
            // Check if we have an authenticated session
            if (!(app.sugarAuth.isAuthenticated())) {
                app.sugarAuth.login({
                    "username": "sally",
                    "password": "sally"
                }, {
                    success: function(data) {
                        console.log("login success");
                        app.router.start();
                        app.router.navigate("", {trigger: true});
                    }, error: function(data) {
                        console.log("login error");
                        console.log(data);
                    }
                });
            } else {
                app.router.navigate("login", {trigger: true});
            }
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
         * @method
         */
        initialize: function(instance, modules) {
            if (modules && _.indexOf(modules, "controller") == -1) {
                return;
            }

            instance.controller = _.extend(module, instance.controller, new Controller({el: app.rootEl}));
        }
    };


    app.events.on("app:init", module.initialize);
    app.augment("controller", module);
})(SUGAR.App);