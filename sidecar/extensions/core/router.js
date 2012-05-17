(function(app) {

    /**
     * Extend the router to add core specific routing functionality.
     * @class Core.Router
     * @singleton
     * @alias SUGAR.App.router
     */
    $.getScript("../../src/core/router.js", function() {
        var CoreRouter = Backbone.Router.extend({
            /**
             * Extends the Routes hash map with core specific routes.
             * @property {Object}
             */
            routes: _.extend(app.router.routes, {
                ":module/goto/:view": "layout"
            }),

            // Routes

            /**
             * Overrides `index` route with core specific index.
             * @param module Module name.
             */
            index: function(module) {
                app.logger.debug("Route changed to index of " + module);
                app.controller.loadView({
                    module: module || "Opportunities",
                    layout: "forecasts"
                });
            }
        });
        var extendedRouter = _.extend(app.router, new CoreRouter())
        app.augment("router", extendedRouter, false);
    }, this);
})(SUGAR.App);