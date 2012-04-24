/*
 * Modification to backbone events to allow unbinding by scope only
 * TODO: Don't put this here, it should be in its own file.
 */

var offByScope = function(scope) {
    _.each(this._callbacks, function(node, ev) {
        if (node.context === scope)
            this.off(ev, node.callback, node.context);
    }, this);
    return this;
};
_.extend(Backbone.Events, {
    offByScope: offByScope
});

_.extend(Backbone.Model.prototype, {
    offByScope: offByScope
});

_.extend(Backbone.View.prototype, {
    offByScope: offByScope
});

/**
 * SideCar Platform
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
 * If you only want to initialize certain modules,
 * <pre><code>var App = SUGAR.App.init({el: "#root", modules: ["router", "controller"]});</code></pre>
 * @class App
 * @singleton
 */
SUGAR.App = (function() {
    var app,
        modules = {};

    /**
     * @constructor Constructor class for the main framework app
     * @param {Object} opts Configuration options
     * @private
     */
    function App(opts) {
        var appId = _.uniqueId("SugarApp_"),
            rootEl;

        // Set parameters
        opts = opts || {};

        /**
         * @cfg {String/Object} el Node or selector of root node for the app.
         */
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
             * Base element to use as the root of the app. This will typically be a jQuery/Zepto node.
             * @property {Object}
             */
            rootEl: rootEl,

            /**
             * Alias to SUGAR.Api
             * @property {Object}
             */
            api: null
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
            /**
             * Set an array of modules you want to be initialized.
             * @cfg {Array} modules
             */
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

            app.events.register(
                /**
                 * @event
                 * This event is fired when a sync process failed
                 */
                "app:sync:error",
                this
            );

            /**
             * Set true if you want to suppress initialization of modules
             * @cfg {Boolean} silent
             */
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

            app.api = SUGAR.Api.getInstance({
                serverUrl: app.config.serverUrl,
                platform: app.config.platform,
                keyValueStore: app.cache
            });

            return app;
        },

        /**
         * Starts the main execution phase of the application.
         * @method
         */
        start: function() {
            if (!(app.api.isAuthenticated())) {
                app.router.login();
            }
            else {
                this.sync();
            }
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
        sync: function(syncsuccess) {
            var self = this;

            async.waterfall([function(callback) {
                app.metadata.sync(callback);
            }, function(metadata, callback) {
                // declare models
                app.data.declareModels(metadata);
                // load viewTemplates
                app.template.load(metadata);

                // load language strings
                if (metadata.appListStrings) {
                    app.lang.setAppListStrings(metadata.appListStrings);
                }
                if (metadata.appStrings) {
                    app.lang.setAppStrings(metadata.appStrings);
                }
                if (metadata.modStrings) {
                    app.lang.setLabels(metadata.modStrings);
                }

                app.acl.set(metadata.acl);

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
                if ($.isFunction(syncsuccess))
                    syncsuccess();
            });
        },

        /**
         * Navigate to a new Layout / View convenience function.
         * @method
         * @param {Core.Context} context Context object to extract module from.
         * @param {Data.Bean} model Model object to route with
         * @param {String} action Desired action, leave blank if
         * @param {Object} params Additional parameters
         */
        navigate: function(context, model, action, params) {
            var route, id, module;
            context = context || app.controller.context;
            model = model || context.get("model");
            id = model.id;
            module = (context.get) ? context.get("module") : model.module;

            route = this.router.buildRoute(module, id, action, params);

            this.router.navigate(route, {trigger: true});
        },

        modules: modules
    };
}());
