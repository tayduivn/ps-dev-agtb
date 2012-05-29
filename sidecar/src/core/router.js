(function(app) {

    /**
     * Gets called before a route gets triggered.
     *
     *
     * The default implementation provides `before` and `after` callbacks that should be executed
     * before and after a route gets triggered.
     *
     * @class Core.Routing
     * @singleton
     * @alias SUGAR.App.routing
     */
    app.augment("routing", {

        /**
         * Checks if a user is authenticated before triggering a route.
         * @param {String} route Route name.
         * @param args(optional) Route parameters.
         * @return {Boolean} Flag indicating if route should be triggered (`true`).
         */
        before: function(route, args) {
            app.logger.trace("BEFORE: " + route);
            // Check if a user is un-athenticated and redirect him to login
            // skip this check for "login" route and all white-listed routes (app.config.unsecureRoutes)
//            if ((route != "login") && !app.api.isAuthenticated()) {
//                app.router.login();
//                return false;
//            }
            return true;
        },

        /**
         * Gets called after a route gets triggered.
         * @param {String} route Route name.
         * @param args(optional) Route parameters.
         */
        after: function(route, args) {
            app.logger.trace("AFTER: " + route);
        }

    });

    /**
     * Router manages the watching of the address hash and routes to the correct handler.
     * @class Core.Router
     * @singleton
     * @alias SUGAR.App.router
     */
    var Router = Backbone.Router.extend({

        // TODO: This router does not support routes that don't require users been authenticated
        // For example, one can not navigate to http://localhost:8888/portal#signup
        // This must be refactored!!!

        /**
         * Routes hash map.
         * @property {Object}
         */
        routes: {
            "": "index",
            "logout": "logout",
            "signup": "signup", // TODO: This route is useless. See comment above
            ":module": "list",
            ":module/layout/:view": "layout",
            ":module/create": "create",
            ":module/:id/:action": "record",
            ":module/:id": "record"
        },

        /**
         * Calls {@link Core.Routing#before} before invoking a route handler
         * and {@link Core.Routing#after} after the handler is invoked.
         *
         * @param {Function} handler Route callback handler.
         * @private
         */
        _routeHandler: function(handler) {
            var args = _.toArray(arguments).splice(1),
                route = handler.route;

            if (app.routing.before(route, args)) {
                app.logger.debug("CONTINUE");
                handler.apply(this, args);
                app.routing.after(route, args);
            }
            else {
                app.logger.debug("REJECTED");
            }
        },

        /**
         * Registeres a handler for a named route.
         *
         * This method wraps the handler into {@link Core.Router#_routeHandler} method.
         *
         * @param {String} route Route expression.
         * @param {String} name Route name.
         * @param {Function/String} callback Route handler.
         */
        route: function(route, name, callback) {
            if (!callback) callback = this[name];
            callback.route = name;
            callback = _.wrap(callback, this._routeHandler);
            Backbone.Router.prototype.route.call(this, route, name, callback);
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
         * Navigates to the previous route in history.
         *
         * This method triggers route change event.
         */
        goBack: function() {
            app.logger.debug("Navigating back...");
            window.history.back();
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

        // ----------------------------------------------------
        // Route handlers
        // ----------------------------------------------------

        /**
         * Handles `index` route.
         *
         * Loads `home` layout for `Home` module or `list` route with default module defined in app.config
         */
        index: function() {
            app.logger.debug("Route changed to index");
            if (app.config.defaultModule) {
                this.list(app.config.defaultModule);
            }
            else {
                this.layout("Home", "home");
            }
        },

        /**
         * Handles `list` route.
         * @param module Module name.
         */
        list: function(module) {
            app.logger.debug("Route changed to list of " + module);
            app.controller.loadView({
                module: module,
                layout: "list"
            });
        },

        /**
         * Handles arbitrary layout for a module that doesn't have a record associated with the layout.
         * @param module Module name.
         * @param layout Layout name.
         */
        layout: function(module, layout) {
            app.logger.debug("Route changed to layout: " + layout + " for " + module);
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
            app.logger.debug("Route changed: create " + module);
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
            app.logger.debug("Loging in");
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
            app.logger.debug("Loging out");
            var self = this;
            app.logout({success: function(data) {
                self.navigate("#");
            }});
        },

        /**
         * Handles `signup` route.
         */
        signup: function() {
            app.logger.debug("Route changed to signup");
            app.controller.loadView({
                module: "Signup",
                layout: "signup",
                create: true
            });
        },

        /**
         * Handles `record` route.
         * @param module Module name.
         * @param id Record ID.
         * @param action(optional) Action name (`edit`, etc.). Defaults to `detail` if not specified.
         */
        record: function(module, id, action) {
            app.logger.debug("Route changed: " + module + "/" + id + "/" + action);

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
