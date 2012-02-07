/**
 SideCar Platform
 *
 */

var SUGAR = SUGAR || {};

SUGAR.App = (function() {
    var app,
        modules = {};

    /**
     * Constructor class for the main framework app
     *
     * @param opts Configuration options
     *  @property el Root node of where the application will be rendered to
     */
    function App(opts) {
        var appId = _.uniqueId("SugarApp_"),
            rootEl;

        // Set parameters
        opts = opts || {};

        if (opts.el) {
            rootEl = (_.isString(opts.el)) ? $(opts.el) : opts.el;
        } else {
            throw "SugarApp needs a root node.";
        }

        // Here we initialize all the modules;
        _.each(modules, function(module) {
            if (module.init && _.isFunction(module.init)) {
                module.init.call(this, this);
            }
        }, this);

        return _.extend({
            appId: appId,
            rootEl: rootEl
        }, this);
    }

    return {
        /**
         * Returns an instance of the app
         * @param opts Pass through configuration options
         */
        init: function(opts) {
            app = app || _.extend(this, new App(opts));
            return app;
        },

        /**
         * Destroys the instance of the current app
         */
        destroy: function() {
            Backbone.history.stop();
            app = null;
        },

        /**
         * Augment the application with a module
         * @param name Name of the module
         * @param obj Module to agument
         * @param init Flag if module should be initialized immediately
         *
         * Module should be an object with an init function.
         * the init function is passed the current instance of
         * the application. Use this to attach your modules to.
         */
        augment: function(name, obj, init) {
            this[name] = obj;
            modules[name] = obj;

            if (init && obj.init && _.isFunction(obj.init)) {
                obj.init.call(app);
            }
        },

        modules: modules
    }
})();