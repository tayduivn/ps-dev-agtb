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
/**
 * Notifications will pull information from the server based on a given delay.
 *
 * Supported properties:
 *
 * - {Number} delay How often (minutes) should the pulling mechanism run.
 * - {Number} limit Limit imposed to the number of records pulled.
 *
 * Example:
 * <pre><code>
 * // ...
 *     array(
 *         'delay' => 5,
 *         'limit' => 4,
 *     ),
 * //...
 * </code></pre>
 *
 * @class View.Views.Base.NotificationsView
 * @alias SUGAR.App.view.views.BaseNotificationsView
 * @extends View.View
 */
({
    plugins: ['Dropdown', 'RelativeTime', 'EllipsisInline', 'Tooltip'],

    /**
     * Notifications bean collection.
     *
     * @property {Data.BeanCollection}
     */
    collection: null,

    /**
     * Interval ID defined when the pulling mechanism is running.
     *
     * @property {Number}
     * @protected
     */
    _intervalId: null,

    /**
     * Default options used when none are supplied through metadata.
     *
     * Supported options:
     * - delay: How often (minutes) should the pulling mechanism run.
     * - limit: Limit imposed to the number of records pulled.
     * - enable_favicon: Enables/disables notifications in favicon, enabled by default.
     *
     * @property {Object}
     * @protected
     */
    _defaultOptions: {
        delay: 5,
        limit: 4,
        enable_favicon: true
    },

    events: {
        'click [data-action=is-read-handler]': 'isReadHandler'
    },

    /**
     * List of array notifications pending transfer to collection.
     * @property {Array}
     * @protected
     */
    _buffer: [],

    /**
     * @property {boolean} is connected to socket server.
     */
    isSocketConnected: false,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        options.module = 'Notifications';

        this._super('initialize', [options]);
        app.events.on('app:sync:complete', this._bootstrap, this);
        app.events.on('app:socket:connect', this.socketOn, this);
        app.events.on('app:socket:disconnect', this.socketOff, this);

        app.events.on('app:notifications:markAs', this.notificationMarkHandler, this);

        app.events.on('app:logout', this.stopPulling, this);
    },

    /**
     * Bootstrap feature requirements.
     *
     * @return {View.Views.BaseNotificationsView} Instance of this view.
     * @protected
     */
    _bootstrap: function() {
        this._initOptions();
        this._initCollection();
        this._initFavicon();

        //Start pulling data after 1 second so that other more important calls to
        //the server can be processed first.
        window.setTimeout(_.bind(this.startPulling, this), 1000);

        this.collection.on('change:is_read', this.render, this);
        return this;
    },

    /**
     * Initialize options, default options are used when none are supplied
     * through metadata.
     *
     * @return {View.Views.BaseNotificationsView} Instance of this view.
     * @protected
     */
    _initOptions: function() {
        var options = _.extend({}, this._defaultOptions, this.meta || {});

        this.delay = options.delay * 60 * 1000;
        this.limit = options.limit;
        this.enableFavicon = options.enable_favicon;

        return this;
    },

    /**
     * Initialize feature collection.
     *
     * @return {View.Views.BaseNotificationsView} Instance of this view.
     * @protected
     */
    _initCollection: function() {
        this.collection = app.data.createBeanCollection(this.module);
        this.collection.options = {
            params: {
                order_by: 'date_entered:desc'
            },
            limit: this.limit,
            myItems: true,
            fields: [
                'date_entered',
                'id',
                'is_read',
                'name',
                'severity'
            ],
            apiOptions: {
                skipMetadataHash: true
            }
        };

        this.collection.filterDef = [{
            is_read: {$equals: false}
        }];

        return this;
    },

    /**
     * Initializes the favicon using the Favico library.
     *
     * This will listen to the collection reset and update the favicon badge to
     * match the value of the notification element.
     *
     * @private
     */
    _initFavicon: function() {

        if (!this.enableFavicon) {
            return;
        }

        this.favicon = new Favico({animation: 'none'});
        this.collection.on('reset', function() {
            var badge = this.collection.length;
            if (this.collection.next_offset > 0) {
                badge = badge + '+';
            }
            this.favicon.badge(badge);
        }, this);

        this.on('render', function(){
            if (!app.api.isAuthenticated() || app.config.appStatus === 'offline') {
                this.favicon.reset();
            }
        });
    },

    /**
     * Start pulling mechanism, executes an immediate pull request and defines
     * an interval which is responsible for executing pull requests on time
     * based interval.
     *
     * @return {View.Views.BaseNotificationsView} Instance of this view.
     */
    startPulling: function() {
        if (!_.isNull(this._intervalId)) {
            return this;
        }
        this.pull();
        this._intervalId = window.setTimeout(_.bind(this._pullAction, this), this.delay);
        return this;
    },

    /**
     * Pulling functionality.
     *
     * @protected
     */
    _pullAction: function() {
        if (!app.api.isAuthenticated() || this.isSocketConnected) {
            this.stopPulling();
            return;
        }

        this._intervalId = window.setTimeout(_.bind(this._pullAction, this), this.delay);

        this.pull();
    },

    /**
     * Stop pulling mechanism.
     *
     * @return {View.Views.BaseNotificationsView} Instance of this view.
     */
    stopPulling: function() {
        if (!_.isNull(this._intervalId)) {
            window.clearTimeout(this._intervalId);
            this._intervalId = null;
        }
        return this;
    },

    /**
     * Pull notifications via bulk API. Render notifications
     * if view isn't disposed or dropdown isn't open.
     *
     * @return {View.Views.BaseNotificationsView} Instance of this view.
     */
    pull: function() {
        if (this.disposed || this.isOpen()) {
            return this;
        }

        var bulkApiId = _.uniqueId();

        this.collection.fetch({
            success: _.bind(this.reRender, this),
            apiOptions: {
                bulk: bulkApiId
            }
        });

        app.api.triggerBulkCall(bulkApiId);

        return this;
    },

    /**
     * Check if dropdown is open.
     *
     * @return {Boolean} `True` if dropdown is open, `false` otherwise.
     */
    isOpen: function() {
        return this.$('[data-name=notifications-list-button]').hasClass('open');
    },

    /**
     * Event handler for notifications.
     *
     * Whenever the user clicks a notification, its `is_read` property is
     * defined as read.
     *
     * We're doing this instead of a plain save in order to
     * prevent the case where an error could occur before the notification get
     * rendered, thus making it as read when the user didn't actually see it.
     *
     * @param {Event} event Click event.
     */
    isReadHandler: function(event) {
        var element = $(event.currentTarget),
            id = element.data('id'),
            notification = this.collection.get(id),
            isRead = notification.get('is_read');

        if (!isRead) {
            notification.set({is_read: true});
        }
    },

    /**
     * Handler listens to global app event for notification record markAs read/unread action
     * and re-renders notifications counter accordingly.
     *
     * @param {Object} model Notification model object
     * @param {boolean} read is notification read?
     */
    notificationMarkHandler: function (model, read) {
        if (read) {
            this.collection.remove(model);
        } else {
            this.collection.add(model);
        }
        this.reRender();
    },

    /**
     * @inheritdoc
     */
    _renderHtml: function() {
        if (!app.api.isAuthenticated() || app.config.appStatus === 'offline') {
            return;
        }

        this._super('_renderHtml');
    },

    /**
     * @inheritdoc
     *
     * Stops pulling for new notifications and disposes the rest.
     */
    _dispose: function() {
        this.stopPulling();
        app.socket.off('notification', this.catchNotification, this);
        app.events.off('app:socket:connect', this.socketOn, this);
        app.events.off('app:socket:disconnect', this.socketOff, this);
        app.events.off('app:notifications:markAs', this.notificationMarkHandler, this);

        this._super('_dispose');
    },

    /**
     * Flush socket buffer to collection
     */
    transferToCollection: function () {
        if (this.collection) {
            while (this._buffer.length) {
                var arr = this._buffer.shift();
                this.collection.add(app.data.createBean(arr._module, arr));
            }
            this.reRender();
        }
    },

    /**
     * Catch notification from socket server and show it.
     *
     * @param data notification from socket server.
     */
    catchNotification: function (data) {
        this._buffer.push(data);
        this.transferToCollection();
    },

    /**
     * On socket and Off pulling
     */
    socketOn: function () {
        this.isSocketConnected = true;
        app.socket.on('notification', this.catchNotification, this);
        this.stopPulling();
    },

    /**
     * Off socket and on pulling
     */
    socketOff: function () {
        this.isSocketConnected = false;
        app.socket.off('notification', this.catchNotification, this);
        this.startPulling();
    },

    /**
     * Render component if it not opened and not disposed.
     * @returns {SUGAR.App.view.views.NotificationsView} Instance of this view.
     */
    reRender: function () {
        if (this.disposed || this.isOpen()) {
            return this;
        }

        return this.render();
    }
})
