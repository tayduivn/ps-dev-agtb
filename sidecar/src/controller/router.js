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
            ":module": "index",
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
            console.log("Router Started");
            var ret = false;

            // Start monitoring hash changes
            // Right now backbone doesn't support checking to see
            // if the history has been started.
            /**try {
                ret =  Backbone.history.start();
            } catch (e) {
                app.logger.error(e.message);
            }**/

            Backbone.history.stop();
            ret = Backbone.history.start();

            return ret;
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

        create: function(module) {
            this.controller.loadView({
                module: module,
                create:true,
                layout: "edit"
            });
        },

        login: function() {
            this.controller.loadView({
                module: "home",
                layout: "login"
            });
        },

        record: function(module, id, action) {

            console.log("====Routing record====");
            console.log("Module: "+ module);
            console.log("Action: "+ action);
            console.log("Id: "+ id);

            action = action || "detail";

            this.controller.loadView({
                module: module,
                id: id,
                action: action,
                layout: action

            });
        },

        buildRoute : function(context, action, model, options){
            var module = options.module || model.module || context.module;
            action = options.action || action;
            var id = model.get ? model.get("id") : model;
            var route = "";
            if (id && module) {
                route = module + "/" + id;
                if (action) {
                    route += "/" + action;
                }
            } else if (module && action) {
                route = module + "/" + action;
            } else if (action) {
                route = action;
            } else if (module) {
                route = module;
            }

            if (options.params) {
                route += "?" + $.param(options.params);
            }
            return route;
        }

    });

    /**
     * @private
     */
    var module = {
        /**
         * Initializes the router however does not start routing. To start routing, call Router.start();
         * @method
         * @param {Object} instance
         */
        initialize: function(instance) {
            if (!instance.controller) {
                throw "app.controller does not exist yet. Cannot create router instance";
            }

            _.extend(module, new Router({controller: instance.controller}));
        }
    }
    app.events.on("app:init", module.initialize);
    app.augment("router", module);
})(SUGAR.App);