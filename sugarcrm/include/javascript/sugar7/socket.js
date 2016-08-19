/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
(function(app) {
    /**
     * Channel class
     * Extends Backbone.Events, parent class can be accessible by this._super.
     * Allows us to join and leave required channel (read rooms) on socket server side.
     *
     * @param {string} name Name of the channel.
     * @param {io} socket Web socket client library of current Socket object.
     * @constructor
     */
    var Channel = function (name) {
        this.systemEvents = _.extend({}, Backbone.Events);
        this._super = Backbone.Events;
        this._name = name;
    };
    _.extend(Channel.prototype, Backbone.Events, {

        /**
         * Returns name of the channel.
         *
         * @returns {string} Name of the channel.
         */
        name: function() {
            return this._name;
        },

        /**
         * Method allows us to subscribe to events of the channel.
         *
         * @returns {Channel} Current object (this) to allow chains.
         */
        on: function() {
            if (this.isEmpty()) {
                this.systemEvents.trigger('join', this.name());
            }
            return this._super.on.apply(this, arguments);
        },

        /**
         * Method allows us to unsubscribe from events of the channel.
         *
         * @returns {Channel} Current object (this) to allow chains.
         */
        off: function() {
            var result = this._super.off.apply(this, arguments);
            if (this.isEmpty()) {
                this.systemEvents.trigger('leave', this.name());
            }
            return result;
        },

        /**
         * Returns true if we have subscribers and false is the channel is empty.
         *
         * @returns {bool} Returns true if we have subscribers and false is the channel is empty.
         */
        isEmpty: function() {
            return _.isEmpty(_.filter(_.values(this._events), _.size));
        }
    });

    /**
     * Socket class
     * Extends Backbone.Events, parent class can be accessible by this._super.
     * Allows us to subscribe on global events and unsubscribe from them.
     * To join and leave specified channels.
     *
     * @param {SUGAR.App} app Instance of current application.
     * @param {Function} lazyTrigger closure to catch subscriptions.
     * @constructor
     */
    var Socket = function(app, lazyTrigger) {
        /**
         * Flag indicating an `app.sync` is in progress.
         *
         * @type {boolean}
         * @private
         */
        this._isSyncing = false;
        this._super = Backbone.Events;
        this._socket = null;
        this._channels = {};
        this._app = app;
        this._socketBinds = {
            onConnect: null,
            onConnectAuth: null,
            onDisconnect: null,
            onMessage: null,
            onClose: null
        };
        var trigger = _.bind(function() {
            this._app.events.on('app:init', this._initConfig, this);
            this._app.events.on('app:notifications:socket:config:changed', this._appSync, this);
        }, this);
        if (lazyTrigger) {
            lazyTrigger(trigger);
        } else {
            trigger();
        }
    };
    _.extend(Socket.prototype, Backbone.Events, {

        /**
         * Entry point for Socket object.
         * That should be executed when application has been initialized and app.config is present.
         * it detects url and forward logic to this._initClientLibrary.
         *
         * @private
         */
        _initConfig: function() {
            if (_.isUndefined(this._app.config.websockets) ||
                _.isUndefined(this._app.config.websockets.client) ||
                _.isUndefined(this._app.config.websockets.client.url)) {
                return;
            }

            if (this._app.config.websockets.client.balancer) {
                this._Factory$().get(this._app.config.websockets.client.url).done(_.bind(function(data) {
                    if (!_.isUndefined(data) && !_.isUndefined(data.location) && !_.isEmpty(data.location)) {
                        this._initClientLibrary(data.location);
                    }
                }, this));
            } else if (!_.isEmpty(this._app.config.websockets.client.url)) {
                this._initClientLibrary(this._app.config.websockets.client.url);
            }
        },

        /**
         * When url was detected we need to load client js library to work with socket.io.
         * When the library has been loaded we forward logic to this._initSocket.
         *
         * @param {string} url URL of Socket Server
         * @private
         */
        _initClientLibrary: function(url) {
            var scriptUrl = url + (url.substr(-1) == '/' ? '' : '/') + 'socket.io/socket.io.js';
            this._Factory$().getScript(scriptUrl, _.bind(function () {
                this._initSocket(url);
            }, this));
        },

        /**
         * Initializes socket.io client library, binds required events and open connection to socket server.
         *
         * @param {string} url URL of Socket Server
         * @private
         */
        _initSocket: function(url) {
            this._socket = this._FactoryIO(url, {
                autoConnect: false
            });
            this._bind();
            this.socket().open();
        },

        /**
         * Subscribes on required events for correct authorization of client and message handling.
         *
         * @private
         */
        _bind: function() {
            this._app.events.on('app:login:success', this.authorize, this);
            this._app.events.on('app:logout', this.authorize, this);
            this._socketBinds.onConnectAuth = _.bind(this.authorize, this);
            this._socketBinds.onConnect = _.bind(this._app.events.trigger, this._app.events, 'app:socket:connect');
            this._socketBinds.onDisconnect =
                _.bind(this._app.events.trigger, this._app.events, 'app:socket:disconnect');
            this._socketBinds.onMessage = _.bind(this._message, this);
            this._socketBinds.onClose = _.bind(this._appSync, this);

            this.socket().on('connect', this._socketBinds.onConnectAuth);

            this.socket().on('connect', this._socketBinds.onConnect);
            this.socket().on('disconnect', this._socketBinds.onDisconnect);

            this.socket().on('message', this._socketBinds.onMessage);

            this.on('close', this._socketBinds.onClose);
        },

        /**
         * Unsubscribes from events.
         *
         * @private
         */
        _unbind: function() {
            this._app.events.off('app:login:success', this.authorize, this);
            this._app.events.off('app:logout', this.authorize, this);
            this.socket().off('connect', this._socketBinds.onConnectAuth);

            this.socket().off('connect', this._socketBinds.onConnect);
            this.socket().off('disconnect', this._socketBinds.onDisconnect);

            this.socket().off('message', this._socketBinds.onMessage);
            this.off('close', this._socketBinds.onClose);

            this._socketBinds = {
                onConnect: null,
                onConnectAuth: null,
                onDisconnect: null,
                onMessage: null,
                onClose: null
            };
        },

        /**
         * Triggers event which depends on received data.
         * If data.channel is present then we trigger event in the channel.
         * If no then we trigger event on current object.
         *
         * @param {Object} data Parameters for triggering of event.
         * @param {string} data.message Name of the event which will be triggered.
         * @param {*} data.args Arguments for the event handler.
         * @param {string|null} data.channel Name of the channel, if it's present then the event will be triggered under specified channel.
         * @private
         */
        _message: function(data) {
            var context = null;
            if (data.channel) {
                if (!_.isUndefined(this._channels[data.channel])) {
                    context = this._channels[data.channel];
                }
            } else {
                context = this;
            }
            if (context) {
                context.trigger(data.message, data.args);
            }
        },

        /**
         * Calls app synchronization.
         *
         * @private
         */
        _appSync: function() {
            if (this._isSyncing) {
                return;
            }
            this._isSyncing = true;
            this._app.sync({
                callback: this._onAppSync.bind(this, this._app.config.websockets),
                params: {__: Math.random()}
            });
        },

        /**
         * Handles changing of socket server's config and reinit socket server connection.
         *
         * @param {Object} oldWebSocketsConfig
         * @private
         */
        _onAppSync: function(oldWebSocketsConfig) {
            if (!_.isEqual(this._app.config.websockets, oldWebSocketsConfig)) {
                if (this.socket()) {
                    this._unbind();
                    this.socket().close();
                }
                this._initConfig();
            }
            this._isSyncing = false;
        },

        /**
         * Sends all required information to socket server to authorize current client.
         */
        authorize: function() {
            this.socket().emit('OAuthToken', {
                'siteUrl': this._getLocation().origin + this._getLocation().pathname,
                'serverUrl': this._app.config.serverUrl,
                'token': this._app.api.getOAuthToken(),
                'channels': this._currentChannels()
            });
        },

        /**
         * Return window.location.
         * @private
         * @return {Location} Return window.location.
         */
        _getLocation: function() {
            return window.location;
        },

        /**
         * Returns list of names of current channels in the socket.
         *
         * @returns {Array} Returns list of names of current channels in the socket.
         * @private
         */
        _currentChannels: function() {
            var result = [];
            _.each(this._channels, function(channel, name) {
                if (!channel.isEmpty()) {
                    result.push(name);
                }
            });
            return result;
        },

        /**
         * Creates new channel object if that's needed and returns it.
         *
         * @param {string} name Name of the required channel.
         * @returns {Channel} Object of Channel class for specified name.
         */
        channel: function(name) {
            if(_.isUndefined(this._channels[name])) {
                this._channels[name] = this._FactoryChannel(name);
                this._channels[name].systemEvents.on('leave', this._destroyChannel, this);
                this._channels[name].systemEvents.on('leave', this._leaveChannel, this);
                this._channels[name].systemEvents.on('join', this._joinChannel, this);
            }
            return this._channels[name];
        },

        /**
         * Join Channels.
         *
         * @param {string} channel name
         * @private
         */
        _joinChannel: function (channel) {
            this._forward('join', channel);
        },

        /**
         * Join Channels.
         *
         * @param {string} channel name
         * @private
         */
        _leaveChannel: function (channel) {
            this._forward('leave', channel);
        },

        /**
         * Forward message.
         *
         * @param {string} action name
         * @param {string} channel name
         */
        _forward: function (action, channel) {
            var socket = this.socket();
            if (socket) {
                socket.emit(action, channel);
            }
        },

        /**
         * Deletes specified channel from the socket.
         *
         * @param {string} name Name of the required channel.
         * @private
         */
        _destroyChannel: function(name) {
            if (!_.isUndefined(this._channels[name])) {
                delete this._channels[name];
            }
        },

        /**
         * Returns current socket object.
         * Created for testing to allow mock of socket object.
         *
         * @returns {io|null}
         */
        socket: function() {
            return this._socket;
        },

        /**
         * Factory method which returns new object of channel class.
         *
         * @param {string} name Name of new channel, will be passed to constructor of Channel class.
         * @returns {Channel} New object of Channel class.
         * @private
         */
        _FactoryChannel: function(name) {
            return new Channel(name);
        },

        /**
         * Factory method which returns new object of socket server client library.
         *
         * @param {string} url URL to socket server.
         * @param {Object} options Key-Value list of options for socket server client library.
         * @returns {io} New object of server client library.
         * @private
         */
        _FactoryIO: function(url, options) {
            return io(url, options);
        },

        /**
         * Factory method which returns jQuery object to do some smart things.
         *
         * @returns {jQuery} Current instance of jQuery.
         * @private
         */
        _Factory$: function() {
            return $;
        }
    });

    app.augment("socket", new Socket(app, typeof lazySocketConstructor == 'function' ? lazySocketConstructor : null ), false);
})(SUGAR.App);
