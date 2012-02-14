(function(app) {

    /**
     * Controller extends from a BackboneView
     * It requires the app to be initialized for the controller init to work
     */
    var Controller = Backbone.View.extend({
        /**
         * Initialize our controller with a context object
         * @private
         * @function
         */
        initialize: function() {
            _.bindAll(this);
            this.context = app.context.getContext();
        },

        /**
         * This is the main entry point from which the router tells the controller
         * what layout to load.
         *
         * @public
         * @function
         * @param params Options to set the global context and the current layout
         *  @option id Current Id of the global context
         *  @option module Current module
         *  @option layout Name of the current layout
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
         * @function
         * @param opts
         *  @option id Id of model (if model)
         *  @option module Module type for data
         * @return obj Data model / collection
         */
        getData: function(opts) {
            var data;

            if (opts.id) {
                data = SUGAR.App.dataManager.fetchBean(opts.module, opts.id);
            } else if (opts.url) {
                // TODO: Make this hit a custom url
            } else {
                data = SUGAR.App.dataManager.fetchBeans(opts.module)
            }

            return data;
        },


        /**
         * Returns a layout from the layout manager
         *
         * @private
         * @function
         * @param opts
         *  @option layout Layout to load
         *  @option module Current module
         * @return obj Layout obj
         */
        getLayout: function(opts) {
            return SUGAR.App.Layout.get({
                layout: opts.layout,
                module: opts.module
            });
        }
    });

    /**
     * Should be auto initialized by the app.
     */
    var module = {
        init: function(instance) {
            instance.controller = instance.controller || _.extend(new Controller({el: app.rootEl}), module);
        }
    };

    app.augment("controller", module);
})(SUGAR.App);