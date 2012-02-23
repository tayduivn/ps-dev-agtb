(function(app) {
    /**
     * This manages the watching of the address hash and routes to the correct handler.
     * @class Router
     * @singleton
     */
    var Router = Backbone.Router.extend({
        /**
         * Routes hash
         * @property {Object}
         */
        routes: {
            "": "index",
            "login": "login",
            ":module/:id/:action": "record",
            ":module/:id": "record"
        },

        /**
         * Initializes the router. Starts the history watcher if it hasn't been started yet.
         * @method
         * @private
         * @param options
         */
        initialize: function(options) {
            _.bindAll(this);

            this.controller = options.controller || null;

            if (!this.controller) {
                throw "No Controller Specified";
            }
        },

        /**
         * Starts the backbone history which in turns starts routing the hashtag
         * @method
         */
        start: function() {
            // Start monitoring hash changes
            // Right now backbone doesn't support checking to see
            // if the history has been started.
            try {
                Backbone.history.start();
            } catch (e) {
                app.logger.error(e.message);
            }
        },

        // Routes

        index: function() {
            this.controller.loadView({
                module: "Contacts", //TODO: This shoudl probably not be Contacts
                layout: "list"
            });
        },

        login: function() {
            this.controller.loadView({
                module: "home",
                layout: "login"
            });
        },

        record: function(module, id, action) {
            this.controller.loadView({
                module: module,
                id: id,
                action: action,
                layout: "LAYOUT NAME"
            });
        }
    });

    /**
     * @private
     */
    var module = {
        /**
         * Initializes the router when an instance is created
         * @method
         * @param {Object} instance
         */
        init: function(instance) {
            if (!instance.controller)
                throw "app.controller does not exist yet. Cannot create router instance";

            _.extend(module, new Router({controller: instance.controller}));

        }
    }

    app.augment("router", module);
})(SUGAR.App);