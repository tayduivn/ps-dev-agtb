(function(app) {
    /**
     * Router manages the watching of the address hash and routes to the correct handler.
     * @class Core.Router
     * @singleton
     * @alias SUGAR.App.router
     */
    var Router = Backbone.Router.extend({
        /**
         * Routes hash map.
         * @property {Object}
         */
        routes: {
            "": "index",
            "logout": "logout",
            ":module": "index",
            ":module/layout/:view": "layout",
            ":module/create": "create",
            ":module/:id/:action": "record",
            ":module/:id": "record"
        },

        /**
         * See `Backbone.Router.navigate` documentation for details.
         *
         * Sidecar overrides this method to redirect the app to the login route if the app is not authenticated.
         * @param {String} fragment URL fragment.
         * @param options(optional) Route options.
         */
        navigate: function(fragment, options) {
            if (!(app.api.isAuthenticated())) {
                Backbone.Router.prototype.navigate.call(this, fragment);
                this.login();
                Backbone.history.stop();
            } else {
                Backbone.Router.prototype.navigate.call(this, fragment, options);
            }
        },

        /**
         * Starts Backbone history which in turn starts routing the hashtag.
         *
         * See Backbone.history documentation for details.
         */
        start: function() {
            app.logger.info("Router Started");
            Backbone.history.stop();
            return Backbone.history.start();
        },

        /**
         * Builds a route.
         *
         * This is a convenience function.
         *
         * @param {Object/String} moduleOrContext The name of the module or a context object to extract the module from.
         * @param {String} id The model's ID.
         * @param {String} action(optional) Action name.
         * @param {Object} params(optional) Additional URL parameters. Should not include id/module/action.
         * @return {String} route The built route.
         */
        buildRoute: function(moduleOrContext, id, action, params) {
            var route;

            if (moduleOrContext) {
                // If module is a context object, then extract module from it
                route = (_.isString(moduleOrContext)) ? moduleOrContext : moduleOrContext.get("module");

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
//            if (params && _.isObject(params) && !_.isEmpty(params)) {
//                route += "?" + $.param(params);
//            }

            return route;
        },

        // Routes

        /**
         * Handles `index` route.
         * @param module Module name.
         */
        index: function(module) {
            app.logger.debug("===Route changed to index of " + module);
            app.controller.loadView({
                module: module || "Cases", //TODO: This should probably not be Casess
                layout: "list"
            });
        },

        /**
         * Handles arbitrary layout for a module that doesn't have a record associated with the layout.
         * @param module Module name.
         * @param layout Layout name.
         */
        layout: function(module, layout) {
            app.logger.debug("===Route changed to layout: " + layout + " for " + module);
            app.controller.loadView({
                module: module,
                layout: layout
            });
        },

        /**
         * Handles `create` route.
         * @param module Module name.
         */
        create: function(module) {
            app.logger.debug("===Route changed: create " + module);
            app.controller.loadView({
                module: module,
                create: true,
                layout: "edit"
            });
        },

        /**
         * Handles `login` route.
         */
        login: function() {
            app.logger.debug("===Loging in");
            app.controller.loadView({
                module: "Login",
                layout: "login",
                create: true
            });
        },

        /**
         * Handles `logout` route.
         */
        logout: function() {
            app.logger.debug("===Loging out");
            var self = this;
            app.logout({success: function(data) {
                self.navigate("#");
            }});
        },

        /**
         * Handles `record` route.
         * @param module Module name.
         * @param id Record ID.
         * @param action(optional) Action name (`edit`, etc.). Defaults to `detail` if not specified.
         */
        record: function(module, id, action) {
            app.logger.debug("===Route changed: " + module + "/" + id + "/" + action);

            action = action || "detail";

            app.controller.loadView({
                module: module,
                modelId: id,
                action: action,
                layout: action
            });
        }
    });

    app.augment("router", new Router(), false);

})(SUGAR.App);
