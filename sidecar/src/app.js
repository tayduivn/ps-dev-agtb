/*
 * Modification to backbone events to allow unbinding by scope only
 * TODO: Don't put this here, it should be in its own file.
 */
/*
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
 */

/**
 * SideCar Platform
 * @ignore
 */
var SUGAR = SUGAR || {};

/**
 * SUGAR.App contains the core instance of the app. All related modules can be found within the SUGAR namespace.
 * An uninitialized instance will exist on page load but you will need to call {@link App#init} to initialize your instance.
 * By default, the app uses `body` element and `div#content` as root element and content element respectively.
 * <pre><code>
 * var app = SUGAR.App.init({
 *      el: "#root",
 *      contentEl: "#content"
 * });
 * </pre></code>
 * If you want to initialize an app without initializing its modules,
 * <pre><code>var app = SUGAR.App.init({el: "#root", silent: true});</code></pre>
 *
 * @class App
 * @singleton
 */
SUGAR.App = (function() {
    var _app,
        _modules = {};

    var _make$ = function(selector) {
        return selector instanceof $ ? selector : $(selector);
    };

    /**
     * @constructor Constructor class for the main framework app
     * @param {Object} opts(optional) Configuration options
     * @return {App} Application instance
     * @private
     */
    function App(opts) {
        var appId = _.uniqueId("SugarApp_");
        opts = opts || {};

        return _.extend({
            /**
             * Unique application ID
             * @property {String}
             */
            appId: appId,

            /**
             * Base element to use as the root of the app.
             *
             * This is a jQuery/Zepto node.
             * @property {Object}
             */
            $rootEl: _make$(opts.el || "body"),

            /**
             * Content element selector.
             *
             * Application controller {@link Core.Controller} loads layouts into the content element.
             * This is a jQuery/Zepto node.
             * @property {Object}
             */
            $contentEl: _make$(opts.contentEl || "#content"),

            /**
             * Alias to SUGAR.Api
             * @property {Object}
             */
            api: null,

            /**
             * Additional components.
             *
             * These components are created and rendered only once when the application starts.
             * Application specific code is responsible for managing the components
             * after they have been put into DOM by the framework.
             */
            additionalComponents: {}

        }, this, Backbone.Events);
    }

    return {
        /**
         * Initializes an app.
         * @param {Object} opts(optional) Configuration options
         *
         * - el: root app element
         * - contentEl: main content element
         * - silent: `true` if you want to suppress initialization of modules
         * @return {App} Application instance
         * @method
         */
        init: function(opts) {
            _app = _app || _.extend(this, new App(opts));

            // Register app specific events
            _app.events.register(
                /**
                 * @event
                 * Fires when the app object is initialized. Modules bound to this event will initialize.
                 */
                "app:init",
                this
            );

            _app.events.register(
                /**
                 * Fires when the application has
                 * finished loading its dependencies and should initialize
                 * everything.
                 *
                 * <pre><code>
                 * obj.on("app:start", callback);
                 * </pre></code>
                 * @event
                 */
                "app:start",
                this
            );

            _app.events.register(
                /**
                 * @event
                 * Fires when the app is beginning to sync data / metadata from the server.
                 */
                "app:sync",
                this
            );

            _app.events.register(
                /**
                 * @event
                 * Fires when the app has finished its syncing process and is ready to proceed.
                 */
                "app:sync:complete",
                this
            );

            _app.events.register(
                /**
                 * @event
                 * Fires when a sync process failed
                 */
                "app:sync:error",
                this
            );

            _app.events.register(
                /**
                 * @event
                 * Fires when login succeeds.
                 */
                "app:login:success",
                this
            );

            _app.events.register(
                /**
                 * @event
                 * Fires when the app logs out.
                 */
                "app:logout",
                this
            );

            _app.events.register(
                /**
                 * Fires when route changes a new view has been loaded.
                 *
                 * <pre><code>
                 * obj.on("app:view:change", callback);
                 * </pre></code>
                 * @event
                 */
                "app:view:change",
                this
            );

            // Instantiate controller: <Capitalized-appId>Controller or Controller.
            var className = _app.utils.capitalize(_app.config ? _app.config.appId : "") + "Controller";
            var Klass = this[className] || this["Controller"];
            this.controller = new Klass();

            // Here we initialize all the modules;
            // TODO DEPRECATED: Convert old style initialization method to noveau style
            _.each(_modules, function(module) {
                if (_.isFunction(module.init)) {
                    module.init(this);
                }
            }, this);

            _app.api = SUGAR.Api.getInstance({
                serverUrl: _app.config.serverUrl,
                platform: _app.config.platform,

                keyValueStore: _app[_app.config.authStore || "cache"],
                clientID: _app.config.clientID
            });

            if (!opts.silent) {
                _app.trigger("app:init", this);
            }

            return _app;
        },

        /**
         * Starts the main execution phase of the application.
         * @method
         */
        start: function() {
            _app.events.registerAjaxEvents();
            _app.trigger("app:start", this);
            _app.router.start();
        },

        /**
         * Destroys the instance of the current app.
         */
        destroy: function() {
            // TODO: Not properly implemented
            if (Backbone.history) {
                Backbone.history.stop();
            }

            _app = null;
        },

        /**
         * Augments the application with a module.
         *
         * Module should be an object with an optional `init(app)` function.
         * The init function is passed the current instance of
         * the application when app's {@link App#init} method gets called.
         * Use the `init` function to perform custom initialization logic during app initialization.
         *
         * @param {String} name Name of the module.
         * @param {Object} obj Module to agument the app with.
         * @param {Boolean} init(optional) Flag indicating if the module should be initialized immediately.
         */
        augment: function(name, obj, init) {
            this[name] = obj;
            _modules[name] = obj;

            if (init && obj.init && _.isFunction(obj.init)) {
                obj.init.call(_app);
            }
        },

        /**
         * Syncs an app.
         *
         * The `app:sync:complete` event will be fired when
         * the series of sync operations have finished.
         * @param {Function} success(optional) Callback function called if sync was successful.
         * @param {Function} error(optional) Callback function called if sync failed.
         * @method
         */
        sync: function(success, error) {
            var self = this;

            async.waterfall([function(callback) {
                self.isSynced = false;
                self.trigger("app:sync");
                _app.metadata.sync(callback);
            }, function(callback) {
                _app.data.declareModels();
                callback(null);
            }], function(err) {
                if (err) {
                    self.trigger("app:sync:error", err);
                    if (_.isFunction(error)) error(err);
                } else {
                    self.isSynced = true;
                    self.trigger("app:sync:complete");
                    if (_.isFunction(success)) success();
                }
            });
        },

        /**
         * Navigates to a new route.
         * @method
         * @param {Core.Context} context(optional) Context object to extract the module from.
         * @param {Data.Bean} model(optional) Model object to route with.
         * @param {String} action(optional) Action name.
         * @param {Object} params(optional) Additional parameters.
         */
        navigate: function(context, model, action, params) {
            var route, id, module;
            context = context || _app.controller.context;
            model = model || context.get("model");
            id = model.id;
            module = context.get("module") || model.module;

            route = this.router.buildRoute(module, id, action, params);
            this.router.navigate(route, {trigger: true});
        },

        /**
         * Logs in this app.
         *
         * @param  {Object} credentials user credentials.
         * @param  {Object} data(optional) extra data to be passed in login request such as client user agent, etc.
         * @param  {Object} callbacks(optional) callback object.
         * @return {Object} XHR request object.
         */
        login: function(credentials, data, callbacks) {
            callbacks       = callbacks || {};
            var origSuccess = callbacks.success,
                origError   = callbacks.error,
                loginData, loadUserCallbacks, loginCallbacks;

            loadUserCallbacks = {
                success: function(data) {
                    if (data.current_user) {
                        _app.user._reset(data ? data.current_user : null);
                    }
                    _app.trigger("app:login:success", loginData);
                    if (origSuccess) origSuccess(loginData);
                },
                error: function(xhr, textStatus, errorThrown) {
                    if (origError) origError(xhr, textStatus, errorThrown);
                    _app.error.handleHttpError(xhr, textStatus);
                }
            };
            loginCallbacks = {
                success: function(data) {
                    var method = 'read', module = 'me';
                    loginData = data;
                    _app.api.records(method, module, {}, {}, loadUserCallbacks);
                },
                error: function(xhr, textStatus, errorThrown) {
                    if (origError) origError(xhr, textStatus, errorThrown);
                    _app.error.handleHttpError(xhr, textStatus);
                }
            };

            return _app.api.login(credentials, data, loginCallbacks);
        },

        /**
         * Logs out this app.
         * @param  {Object} callbacks(optional) callback object.
         * @param {Boolean} clear(optional) Flag indicating if user information must be deleted from local storage.
         * @return {Object} XHR request object.
         */
        logout: function(callbacks, clear) {
            var originalSuccess, originalError, xhr;
            callbacks = callbacks || {};
            originalSuccess = callbacks.success;
            originalError = callbacks.error;

            callbacks.success = function(data) {
                // TODO: The user.js module now listens for logout event.
                // It takes a 'clear' boolean indicating whether we want
                // to completely clear user's data or leave intact. Later,
                // we should let user choose. For they get "zapped"
                _app.trigger("app:logout", clear);
                if (originalSuccess) {
                    originalSuccess(data);
                }
            };
            callbacks.error = function(xhr, textStatus, errorThrown) {
                if (originalError) originalError(xhr, textStatus, errorThrown);
                _app.error.handleHttpError(xhr, textStatus);
            };

            xhr = _app.api.logout(callbacks);
            return xhr;
        },

        modules: _modules
    };

}());
