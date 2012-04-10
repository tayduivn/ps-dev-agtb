(function(app) {
    /**
     * This manages the watching of the address hash and routes to the correct handler.
     * @class Core.Router
     * @singleton
     * @alias SUGAR.App.router
     */
    var Router = Backbone.Router.extend({
        /**
         * Routes hash
         * @property {Object}
         */
        routes: {
            "": "index",
            "login": "login",
            ":module": "index",
            ":module/layout/:view": "layout",
            ":module/list": "index",
            ":module/create": "create",
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
            app.logger.info("Router Started");
            var ret = false;

            Backbone.history.stop();
            ret = Backbone.history.start();

            return ret;
        },


        /**
         * Convenience function to build routes
         *
         * @param {Object/String} module The name of the module or a context object to extract the module from
         * @param {String} id The model's ID
         * @param {String} action Action to perform. This is an optional param
         * @param {Object} options Additional parameters. Should not include id/module/action.
         * @return {String} route The built route
         */
        buildRoute : function(module, id, action, params) {
            var route;

            if (module) {
                // If module is a context object, then extract module from it
                route = (_.isString(module)) ? module : module.get("module");

                if (id) {
                    route += "/" + id;
                }

                if (action) {
                    route += "/" + action;
                }
            } else {
                route = action;
            }

            // TODO: Currently not supported and breaks other routes
            // return (_.isObject(params)) ? route += "/?" + $.param(params) : route;
            return route;
        },

        // Routes

        index: function() {
            this.controller.loadView({
                module: "Cases", //TODO: This shoudl probably not be Casess
                layout: "list"
            });
        },

        list: function(module) {
            this.controller.loadView({
                module: module,
                layout: "list"
            });
        },

        /**
         *  The layout route/callback is used to load an arbitrary layout for a module that doesn't have a record associated with the layout.
         * @param module
         * @param layout
         */
        layout: function(module, layout) {
            this.controller.loadView({
                module: module,
                layout: layout
            });
        },

        create: function(module) {
            this.controller.loadView({
                module: module,
                create:true,
                layout: "edit"
            });
        },

        login: function() {
            this.controller.loadView({
                module: "Home",
                layout: "login",
                create: true
            });
        },

        record: function(module, id, action) {
            console.log("====Routing record====");
            console.log("Module: ", module, "Action: ", action, "Id: ", id);

            action = action || "detail";

            this.controller.loadView({
                module: module,
                id: id,
                action: action,
                layout: action
            });
        }
    });

    /**
     * @private
     */
    var module = {
        /**
         * Initializes the router however does not start routing. To start routing, call Router.start();
         *
         * @method
         * @param {Object} instance The instance of the App
         * @param {Array} modules An optional list of modules to initialize
         */
        initRouter: function(instance, modules) {
            if (!instance.controller) {
                throw "app.controller does not exist yet. Cannot create router instance";
            }

            if (modules && _.indexOf(modules, "controller") == -1) {
                return;
            }

            _.extend(module, new Router({controller: instance.controller}));
        }
    };

    app.events.on("app:init", module.initRouter);
    app.augment("router", module);
})(SUGAR.App);