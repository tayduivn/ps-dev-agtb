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
            app.events.publish("app:start", this);
            this.poop = "100";
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
            this.data = {};
            this.layout = null;

            this.data = this.getData(params);
            this.context.init(params, this.data);
            this.layout = this.getLayout(params);

            // Render the rendered layout to the main element
            this.$el.html(this.layout.$el);

            // Render the layout
            this.layout.render();
        },

        /**
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
                model : bean,
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
         * Starts the application. Call this function when all the dependencies have been loaded.
         * @method
         */
        start: function() {

            // Check if we have an authenticated session
            if (!(app.sugarAuth.isAuthenticated())) {
                app.sugarAuth.login({"username":"sally","password":"sally"},{success:function(data){console.log("login success"); app.router.start(); app.router.navigate("", {trigger: true});},error:function(data){console.log("login error"); console.log(data);}})
            } else {
                app.router.navigate("", {trigger: true});
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
        init: function(instance) {
            instance.controller = _.extend(module, instance.controller, new Controller({el: app.rootEl}));
        }
    };

    app.augment("controller", module);
})(SUGAR.App);