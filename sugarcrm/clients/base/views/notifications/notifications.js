/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
/**
 * Notifications will pull information from the server based on a delay given.
 *
 * Supported properties:
 *
 * - {Integer} delay How often (minutes) should the pulling mechanism run.
 * - {Integer} limit Limit imposed to the number of records pulled.
 * - {Object} severity_css An object where its keys map to a specific
 * notification severity and values to a matching CSS class.
 *
 * Example:
 * <pre><code>
 * // ...
 *     array(
 *         'delay' => 5,
 *         'limit' => 4,
 *         'severity_css' => array(
 *             'alert' => 'label-important',
 *             'information' => 'label-info',
 *             'other' => 'label-inverse',
 *             'success' => 'label-success',
 *             'warning' => 'label-warning',
 *         ),
 *     ),
 * //...
 * </code></pre>
 *
 * @class View.Views.BaseNotificationsView
 * @alias SUGAR.App.view.views.BaseNotificationsView
 */
({
    plugins: ['Dropdown', 'Timeago', 'EllipsisInline', 'Tooltip'],

    /**
     * Notifications bean collection.
     *
     * @property {Data.BeanCollection}
     */
    collection: null,

    /**
     * Collections for additional modules.
     */
    _alertsCollections: {},

    /**
     * @property {Number} Timestamp when we started pooling.
     */
    dateStarted: null,

    /**
     * @property {Number} Interval ID for checking reminders.
     */
    _remindersIntervalId: null,

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
     * - severity_css: An object where its keys map to a specific notification
     * severity and values to a matching CSS class.
     *
     * @property {Object}
     * @protected
     */
    _defaultOptions: {
        delay: 5,
        limit: 4,
        severity_css: {
            alert: 'label-important',
            information: 'label-info',
            other: 'label-inverse',
            success: 'label-success',
            warning: 'label-warning'
        }
    },

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        options.module = 'Notifications';

        this._super('initialize', [options]);
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
        this._initReminders();
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
        this.severityCss = options.severity_css;

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
            fields: ['date_entered', 'id', 'name', 'severity']
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
     * Initialize reminders for Calls and Meetings.
     *
     * Setup the reminderMaxTime that is based on maximum reminder time option
     * added to the pulls delay to get a big interval to grab for possible
     * reminders.
     * Setup collections for each module that we support with reminders.
     *
     * FIXME this will be removed when we integrate reminders with
     * Notifications on server side. This is why we have modules hardcoded.
     * We also don't check for meta as optional because it is required.
     * We will keep all this code private because we don't want to support it
     *
     * @private
     */
    _initReminders: function() {

        var timeOptions = _.keys(app.lang.getAppListStrings('reminder_time_options')),
            max = _.max(timeOptions, function(key) {
            return parseInt(key, 10);
        });

        this.reminderMaxTime = parseInt(max, 10) + this.delay / 1000;
        this.reminderDelay = 30 * 1000;

        _.each(['Calls', 'Meetings'], function(module) {
            this._alertsCollections[module] = app.data.createBeanCollection(module);
            this._alertsCollections[module].options = {
                limit: this.meta.remindersLimit,
                fields: ['date_start', 'id', 'name', 'reminder_time', 'location']
            };
        }, this);

        return this;
    },

    /**
     * Retrieve label according to supplied severity.
     *
     * @param {String} severity Notification severity.
     * @return {String} Matching label or severity if supplied severity
     *   doesn't exist.
     */
    getSeverityLabel: function(severity) {
        var list = app.lang.getAppListStrings('notifications_severity_list');
        return list[severity] || severity;
    },

    /**
     * Retrieve CSS class according to supplied severity.
     *
     * @param {String} severity Notification severity.
     * @return {String} Matching css class or an empty string if supplied
     * severity doesn't exist.
     */
    getSeverityCss: function(severity) {
        return this.severityCss[severity] || '';
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
            return this;
        }
        this.dateStarted = new Date().getTime();
        var self = this;

        this.pull();
        this._pullReminders();
        this._intervalId = window.setTimeout(_.bind(this._pullAction, this), this.delay);
        this._remindersIntervalId = window.setTimeout(_.bind(this.checkReminders, this), this.reminderDelay);
        return this;
    },

    /**
     * Pulling functionality.
     *
     * @protected
     */
    _pullAction: function() {
        if (!app.api.isAuthenticated()) {
            this.stopPulling();
            return;
        }
        var diff = this.delay - (new Date().getTime() - this.dateStarted) % this.delay;
        this._intervalId = window.setTimeout(_.bind(this._pullAction, this), diff);

        this.pull();
        this._pullReminders();
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
        if (!_.isNull(this._remindersIntervalId)) {
            window.clearInterval(this._remindersIntervalId);
            this._remindersIntervalId = null;
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
            return this;
        }

        var self = this;

        this.collection.fetch({
            success: function() {
                if (self.disposed || self.isOpened()) {
                    return this;
                }

                self.render();
            }
        });

        return this;
    },

    /**
     * Pull next reminders from now to the next remindersMaxTime.
     *
     * This will give us all the reminders that should be triggered during the
     * next maximum reminders time (with pull delay).
     */
    _pullReminders: function() {

        if (this.disposed) {
            return this;
        }

        var date = new Date(),
            startDate = date.toISOString(),
            endDate;

        date.setTime(date.getTime() + this.reminderMaxTime * 1000);
        endDate = date.toISOString();

        _.each(['Calls', 'Meetings'], function(module) {

            this._alertsCollections[module].filterDef = _.extend({},
                this.meta.remindersFilterDef || {},
                {
                    'date_start': {'$dateBetween': [startDate, endDate]},
                    'users.id': {'$equals': app.user.get('id')}
                }
            );
            this._alertsCollections[module].fetch({
                silent: true,
                merge: true,
                //Notifications should never trigger a metadata refresh
                apiOptions: {skipMetadataHash: true}
            });
        }, this);

        return this;
    },

    /**
     * Check if there is a reminder we should show in the near future.
     *
     * If the reminder exists we immediately show it.
     *
     * @return {View.Notifications} Instance of this view.
     */
    checkReminders: function() {
        if (!app.api.isAuthenticated()) {
            this.stopPulling();
            return this;
        }
        var date = new Date(),
            diff = this.reminderDelay - (date.getTime() - this.dateStarted) % this.reminderDelay;
        this._remindersIntervalId = window.setTimeout(_.bind(this.checkReminders, this), diff);
        _.each(this._alertsCollections, function(collection) {
            _.chain(collection.models)
                .filter(function(model) {
                    var needDate = new Date(model.get('date_start')) - parseInt(model.get('reminder_time'), 10) * 1000;
                    return needDate > date && needDate - date <= diff;
                }, this)
                .each(this._showReminderAlert, this);
        }, this);
        return this;
    },

    /**
     * Show reminder alert based on given model.
     *
     * @param {Backbone.Model} model Model that is triggering a reminder.
     *
     * @private
     */
    _showReminderAlert: function(model) {
        var url = app.router.buildRoute(model.module, model.id),
            dateFormat = app.user.getPreference('datepref') + ' ' + app.user.getPreference('timepref'),
            dateValue = app.date.format(new Date(model.get('date_start')), dateFormat),
            template = app.template.getView('notifications.notifications-alert'),
            message = template({
                title: app.lang.get('LBL_REMINDER_TITLE', model.module),
                module: model.module,
                model: model,
                location: model.get('location'),
                description: model.get('description'),
                dateStart: dateValue
            });
        _.defer(function() {
            if (confirm(message)) {
                app.router.navigate(url, {trigger: true});
            }
        });
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
     * If notifications collection is available and has models, two 'severity'
     * related properties are injected into each model:
     * - severityCss: Model severity matching CSS class.
     * - severityLabel: Model severity label.
     *
     * {@inheritDoc}
     */
    _renderHtml: function() {
        if (!app.api.isAuthenticated() || app.config.appStatus === 'offline') {
            return;
        }

        if (!_.isObject(this.collection)) {
            this._super('_renderHtml');
            return;
        }

        _.each(this.collection.models, function(model) {
            model.set('severityCss', this.getSeverityCss(model.get('severity')));
            model.set('severityLabel', this.getSeverityLabel(model.get('severity')));
        }, this);

        this._super('_renderHtml');
    },

    /**
     * {@inheritDoc}
     *
     * Stops pulling for new notifications and disposes all reminders.
     */
    _dispose: function() {
        this.stopPulling();
        this._alertsCollections = {};

        this._super('_dispose');
    }
})
