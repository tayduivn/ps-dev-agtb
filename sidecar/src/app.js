

/**
 * Modification to backbone events to allow unbinding by scope only
 * TODO: Don't put this here, it should be in its own file.
 */

var offByScope = function(scope){
    var calls;
    calls = this._callbacks;
    _.each(calls, function(node, ev){
        if (node.context === scope)
            this.off(ev, node.callback, node.context);
    }, this);
    return this;
};
_.extend(Backbone.Events, {
    offByScope : offByScope
});

_.extend(Backbone.Model.prototype, {
    offByScope : offByScope
});

_.extend(Backbone.View.prototype, {
    offByScope : offByScope
});

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
 * @class App
 * @singleton
 */
SUGAR.App = (function() {
    var app,
        modules = {};

    /**
     * @constructor Constructor class for the main framework app
     *
     * @param {Object} opts Configuration options
     *
     * <ul>
     *     <li>el: Root node of where the application will be rendered to. Could be a jQuery node or selector</li>
     * </ul>
     *
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
         * @return {Object} Application instance
         * @method
         */
        init: function(opts) {
            opts.modules = opts.modules || {};
            app = app || _.extend(this, new App(opts));

            // Register app specific events
            app.events.register("app:init", this);
            app.events.register("app:sync:complete", this);
            app.events.register("app:sync:error", this);

            if (!opts.silent) {
                app.trigger("app:init", this, opts.modules);
            }

            // Here we initialize all the modules;
            _.each(modules, function(module, key) {
                if (_.isFunction(module.init)) {
                    module.init(this);
                }
            }, this);

            return app;
        },

        /**
         * Starts the application. A shortcut method to {@link Controller#start}.
         */
        start: function() {
            this.sync();
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
                if (app.Offline) {
                    app.Offline.dataManager.migrate(metadata, {callback: callback});
                }
                else {
                    callback(null, metadata);
                }
            }, function(metadata, callback) {
                app.dataManager.declareModels(metadata);
                callback(null, metadata);
            }], function(err, result) {
                if (err) {
                    app.logger.error(err);
                    self.trigger("app:sync:error", err);
                }
                else {
                    // Result should be metadata
                    self.trigger("app:sync:complete", result);
                }
            });
        },

        modules: modules
    };
})();