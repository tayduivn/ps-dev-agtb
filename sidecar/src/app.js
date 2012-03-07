/**
 SideCar Platform
 * @ignore
 */
var SUGAR = SUGAR || {};

/**
 * SUGAR.App contains the core instance of the app. All related modules can be found within the SUGAR namespace.
 * An uninitialized instance will exist on page load but you will need to call {@link App#init} to initialize your instance.
 * <pre><code>
 * var App = SUGAR.App.init({el: "#root"});
 * </pre></code>
 * If you want to initialize an app without initializing its modules,
 * <pre><code>var App = SUGAR.App.init({el: "#root", silent: true});</code></pre>
 * @class App
 * @singleton
 */
SUGAR.App = (function() {
    var app,
        modules = {};

    /**
     * @constructor Constructor class for the main framework app
     * @param {Object} opts Configuration options
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

        return _.extend({
            /**
             * Unique Application ID
             * @property {String}
             */
            appId: appId,

            /**
             * Base element to use as the root of the App
             * @property {jQuery Node}
             */
            rootEl: rootEl,

            /**
             * Alias to SUGAR.Api
             * @property {Object}
             */
            api: SUGAR.Api.getInstance({
                baseUrl: opts.rest || "/rest/v10" // TODO: Change this default
            })
        }, this, Backbone.Events);
    }

    return {
        /**
         * Returns an instance of the app
         * @param {Object} opts Pass through configuration options
         * <ul>
         *     <li>el: Root node of where the application will be rendered to. Could be a jQuery node or selector</li>
         *     <li>rest: Rest url root</li>
         *     <li>silent: Set true if you want to suppress initialization of modules</li>
         *     <li>modules: Set an array of modules you want to be initialized</li>
         * </ul>
         * @return {Object} Application instance
         * @method
         */
        init: function(opts) {
            opts.modules = opts.modules || {};
            app = app || _.extend(this, new App(opts));

            // Register app specific events

            app.events.register(
                /**
                 * @event
                 * Starts the initialization phase of the app. Modules bound to this event will initialize.
                 */
                "app:init",
                this
            );

            app.events.register(
                /**
                 * @event
                 * This event is fired when the app is beginning to sync data / metadata from the server.
                 */
                "app:sync",
                this
            );

            app.events.register(
                /**
                 * @event
                 * This event is fired when the app has finished its syncing process and is ready to proceed.
                 */
                "app:sync:complete",
                this
            );

            // If the silent flag is set, we do not want to initialize any of the app's modules.
            if (!opts.silent) {
                app.trigger("app:init", this, opts.modules);
            }

            // Here we initialize all the modules;
            // TODO DEPRECATED: Convert old style initialization method to noveau style
            _.each(modules, function(module, key) {
                if (_.isFunction(module.init)) {
                    module.init(this);
                }
            }, this);

            return app;
        },

        /**
         * Starts the application. A shortcut method to {@link Controller#start}.
         * @method
         */
        start: function() {
            this.sync();
        },

        /**
         * Destroys the instance of the current app
         * TODO: Not properly implemented
         * @method
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

        /**
         * Calls a global sync for the app. An app:sync:complete event will be fired when
         * the series of sync operations have finished.
         * @method
         */
        sync: function() {
            var self = this;

            async.waterfall([function(callback) {
                app.metadata.sync(callback);
            }, function(metadata, callback) {
                app.dataManager.declareModels(metadata);
                callback(null, metadata);
            }], function(err, result) {
                // Result should be metadata
                self.trigger("app:sync:complete", result);
            });
        },

        modules: modules
    };
})();