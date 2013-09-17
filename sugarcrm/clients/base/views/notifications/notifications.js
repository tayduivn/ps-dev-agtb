({
    tagName: 'ul',

    className: 'nav pull-right megamenu',

    // FIXME: dropdown plugin should not be needed when SC-1214 gets fixed
    plugins: ['dropdown', 'timeago', 'ellipsis_inline','tooltip'],

    // FIXME: open event should not be needed when SC-1214 gets fixed
    events: {
        'click [data-action=open]': 'open',
        'mouseenter [rel="tooltip"]': 'showTooltip',
        'mouseleave [rel="tooltip"]': 'hideTooltip'
    },

    /**
     * Notifications bean collection.
     *
     * @property {Data.BeanCollection}
     */
    collection: null,

    /**
     * Interval ID defined when the pulling mechanism is running.
     *
     * @property {Integer}
     * @protected
     */
    _intervalId: null,

    /**
     * Default options used when none are supplied through metadata.
     *
     * Supported options:
     * - delay: How often (minutes) should the pulling mechanism run.
     * - limit: Limit imposed to the number of records pulled.
     * - level_css: An object where its keys map to a specific notification
     * level and values to a matching CSS class.
     *
     * @property {Object}
     * @protected
     */
    _defaultOptions: {
        delay: 5,
        limit: 4,
        level_css: {
            alert: 'label-important',
            information: 'label-info',
            other: 'label-inverse',
            success: 'label-success',
            warning: 'label-warning'
        }
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        options.module = 'Notifications';

        app.view.View.prototype.initialize.call(this, options);
        app.events.on('app:sync:complete', this._bootstrap, this);
        app.events.on('app:logout', this.stopPulling, this);
    },

    /**
     * Bootstrap feature requirements.
     *
     * @return {View.Notifications} Instance of this view.
     * @protected
     */
    _bootstrap: function() {
        this._initOptions();
        this._initCollection();
        this.startPulling();
        return this;
    },

    /**
     * Initialize options, default options are used when none are supplied
     * through metadata.
     *
     * @return {View.Notifications} Instance of this view.
     * @protected
     */
    _initOptions: function() {
        var options = _.extend(this._defaultOptions, this.meta || {});

        this.delay = options.delay * 60 * 1000;
        this.limit = options.limit;
        this.levelCss = options.level_css;

        return this;
    },

    /**
     * Initialize feature collection.
     *
     * @return {View.Notifications} Instance of this view.
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
            fields: ['date_entered', 'id', 'name', 'level']
        };

        this.collection.sync = _.wrap(
            this.collection.sync,
            function(sync, method, model, options) {
                options = options || {};
                options.endpoint = function(method, model, options, callbacks) {
                    var url = app.api.buildURL(model.module, 'pull', {}, options.params);
                    return app.api.call('read', url, {}, callbacks);
                };

                sync(method, model, options);
            }
        );

        return this;
    },

    /**
     * Retrieve label according to supplied level.
     *
     * @param {String} level Notification level.
     * @return {String} Matching label or level if supplied level doesn't exist.
     */
    getLevelLabel: function(level) {
        var list = app.lang.getAppListStrings('notifications_level_list');
        return list[level] || level;
    },

    /**
     * Retrieve CSS class according to supplied level.
     *
     * @param {String} level Notification level.
     * @return {String} Matching css class or an empty string if supplied level
     * doesn't exist.
     */
    getLevelCss: function(level) {
        return this.levelCss[level] || '';
    },

    /**
     * Start pulling mechanism, executes an immediate pull request and defines
     * an interval which is responsible for executing pull requests on time
     * based interval.
     *
     * @return {View.Notifications} Instance of this view.
     */
    startPulling: function() {
        if (!_.isNull(this._intervalId)) {
            return;
        }

        var self = this;

        this.pull();
        this._intervalId = window.setInterval(function() {
            if (!app.api.isAuthenticated()) {
                self.stopPulling();
                return;
            }

            self.pull();
        }, this.delay);

        return this;
    },

    /**
     * Stop pulling mechanism.
     *
     * @return {View.Notifications} Instance of this view.
     */
    stopPulling: function() {
        if (!_.isNull(this._intervalId)) {
            window.clearInterval(this._intervalId);
            this._intervalId = null;
        }

        return this;
    },

    /**
     * Pull and render notifications, if view isn't disposed or dropdown isn't
     * opened.
     *
     * @return {View.Notifications} Instance of this view.
     */
    pull: function() {
        if (this.disposed || this.isOpened()) {
            return;
        }

        var self = this;

        this.collection.fetch({
            success: function() {
                if (self.disposed || self.isOpened()) {
                    return;
                }

                self.render();
            }
        });

        return this;
    },

    /**
     * FIXME: this should not be needed when SC-1214 gets fixed, toggle
     * mechanism is handled by default by twitter _bootstrap, '.dtoggle' should
     * also be removed from navigation bar button
     *
     * @deprecated
     */
    open: function(event) {
        if (this.disposed) {
            return;
        }

        var $target = this.$(event.target);
        this.toggleDropdownHTML($target);
    },

    /**
     * Check if dropdown is opened.
     *
     * @return {Boolean} True if dropdown is opened, false otherwise.
     */
    isOpened: function() {
        return this.$('.notification-list').hasClass('open');
    },

    /**
     * If notifications collection is available and has models, two 'level'
     * related properties are injected into each model:
     * - levelCss: Model level matching CSS class.
     * - levelLabel: Model level label.
     *
     * @inheritdoc
     */
    _renderHtml: function() {
        if (!app.api.isAuthenticated() || app.config.appStatus === 'offline') {
            return;
        }

        if (!_.isObject(this.collection)) {
            app.view.View.prototype._renderHtml.call(this);
            return;
        }

        _.each(this.collection.models, function(model) {
            model.set('levelCss', this.getLevelCss(model.get('level')));
            model.set('levelLabel', this.getLevelLabel(model.get('level')));
        }, this);

        app.view.View.prototype._renderHtml.call(this);
    },
    showTooltip: function(event) {
        this.$(event.currentTarget).tooltip("show");
    },

    hideTooltip: function(event) {
        this.$(event.currentTarget).tooltip("hide");
    },
    /**
     * @inheritdoc
     */
    _dispose: function() {
        this.stopPulling();
        this.collection.off();
        app.view.View.prototype._dispose.call(this);
    }
})
