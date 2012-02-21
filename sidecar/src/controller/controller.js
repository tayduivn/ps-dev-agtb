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
            _.bindAll(this);
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
            this.layout = this.getLayout(params);
            this.context.init(params, this.data);

            // Render the layout
            this.layout.render();

            // Render the rendered layout to the main element
            this.$el.html(this.layout.$el);
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
            var data;

            if (opts.id) {
                data = app.dataManager.fetchBean(opts.module, opts.id);
            } else if (opts.url) {
                // TODO: Make this hit a custom url
            } else {
                data = app.dataManager.fetchBeans(opts.module)
            }

            return data;
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
            return SUGAR.App.Layout.get({
                layout: opts.layout,
                module: opts.module
            });
        },

        /**
         * Starts the application. Call this function when all the dependencies have been loaded.
         * @method
         */
        start: function() {

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
            instance.controller = instance.controller || _.extend(new Controller({el: app.rootEl}), module);
        }
    };

    app.augment("controller", module);
})(SUGAR.App);