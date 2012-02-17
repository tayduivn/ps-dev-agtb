/**
 SideCar Platform
 * @ignore
 */
var SUGAR = SUGAR || {};

/**
 * @class App
 * @singleton
 *
 */
SUGAR.App = (function() {
    var app,
        modules = {};

    /**
     * Constructor class for the main framework app
     *
     * @constructor
     * @param {Object} opts Configuration options
     *  @option el Root node of where the application will be rendered to
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
            if (_.isFunction(module.init)) {
                module.init(this);
            }
        }, this);

        return _.extend({
            /**
             * @property {String}
             */
            appId: appId,

            /**
             * @property {jQuery Node}
             */
            rootEl: rootEl
        }, this);
    }

    return {
        /**
         * Returns an instance of the app
         * @param {Object} opts Pass through configuration options
         * @return {Object} Application instance
         * @method
         */
        init: function(opts) {
            app = app || _.extend(this, new App(opts));
            return app;
        },

        /**
         * Destroys the instance of the current app
         * TODO: Not properly implemented
         */
        destroy: function() {
            if (Backbone.history) {
                Backbone.history.stop();
            }

            app = null;
        },

        /**
         * Augment the application with a module
         * @param {String} name Name of the module
         * @param {Object} obj Module to agument
         * @param {Boolean} init Flag if module should be initialized immediately
         * @method
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