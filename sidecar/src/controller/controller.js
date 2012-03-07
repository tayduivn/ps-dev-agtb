(function(app) {
    /**
     * Controller manages the loading and unloading of Views within the app. It extends from a Backbone.View.
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
         * This is the main entry point from which the router tells the controller
         * what layout to load.
         *
         * @method
         * @param {Object} params Options to set the global context and the current layout
         * <ul>
         *  <li>id: Current Id of the global context</li>
         *  <li>module: Current module</li>
         *  <li>layout: Name of the current layout</li>
         * </ul>
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
         * DEPRECATED?
         * Retrieves data based on the params. If the parameters include an id,
         * then a model is returned, else a collection is returned.
         *
         * @private
         * @method
         * @param {Object} opts
         *  @option id Id of model (if model)
         *  @option module Module type for data
         * @return {Object} obj Data model / collection
         */
        getData: function(opts) {
            var data, bean, collection;

            if (opts.id) {
                bean = app.dataManager.fetchBean(opts.module, opts.id);
                collection = app.dataManager.createBeanCollection(opts.module, [bean]);
            }
            else if (opts.create) {
                bean = app.dataManager.createBean(opts.module);
                collection = app.dataManager.createBeanCollection(opts.module, [bean]);
            }
            else if (opts.url) {
                // TODO: Make this hit a custom url
            } else {
                collection = app.dataManager.fetchBeans(opts.module);
                bean = collection.models[0] || {};
            }

            return {
                model: bean,
                collection: collection
            };
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
         * Callback function once the app.sync() finishes. This should check if
         * the current user has authenticated or not and handle the redirection
         * if necessary.
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