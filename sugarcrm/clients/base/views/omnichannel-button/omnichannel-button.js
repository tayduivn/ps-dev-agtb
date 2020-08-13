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
 * 'Omnichannel' button.
 *
 * @class View.Views.Base.OmnichannelButtonView
 * @alias SUGAR.App.view.views.BaseOmnichannelButtonView
 * @extends View.View
 */
({
    className: 'omni-button',

    events: {
        'click [data-action=omnichannel]': 'openConsole'
    },

    /**
     * Opens console.
     */
    openConsole: function() {
        var console = this._getConsole();
        if (console) {
            console.open();
            this.$('.btn').removeClass('notification-pulse');
        }
    },

    /**
     * Sets button status.
     *
     * @param {string} status string: logged-out, logged-in, active-session
     */
    setStatus: function(status) {
        var currentStatus = this.status || 'logged-out';
        var button = this.$('.btn');
        button.removeClass(currentStatus);
        button.addClass(status);
        this.status = status;
    },

    /**
     * @inheritdoc
     */
    _renderHtml: function() {
        this.isAvailable = app.api.isAuthenticated() && // user has logged in
            !!app.config.awsConnectInstanceName && // aws connect is configured
            _.indexOf(app.user.get('licenses'), 'SUGAR_SERVE') !== -1; // user has serve license
        this._super('_renderHtml');
    },

    /**
     * Creates omnichannel console if not yet.
     *
     * @return {View.Layout} The console
     * @private
     */
    _getConsole: function() {
        if (_.isUndefined(app.omniConsole)) {
            var context = app.controller.context.getChildContext({forceNew: true, module: 'Dashboards'});
            // remove it from parent so that it won't get cleared when loading a new view
            app.controller.context.children.pop();
            var console = app.view.createLayout({
                type: 'omnichannel-console',
                context: context
            });
            console.initComponents();
            console.loadData();
            console.$el.hide();
            console.render();
            this._bindConsoleListeners(console);
            $('#sidecar').append(console.$el);
            app.omniConsole = console;
        }
        return app.omniConsole;
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        if (!_.isUndefined(app.omniConsole)) {
            app.omniConsole.context.off('omnichannel:auth');
            app.omniConsole.off('omnichannel:message');
            app.omniConsole.off('omniconsole:open');
        }
        this._super('_dispose');
    },

    /**
     * Show user notification if the console is closed when a message comes in
     *
     * @private
     */
    _notifyUser: function() {
        var omniConsole = this._getConsole();
        if (!omniConsole.isOpen()) {
            this.$('.btn').addClass('notification-pulse');
        }
    },

    /**
     * Clear notifications
     *
     * @private
     */
    _clearNotifications: function() {
        this.$('.btn').removeClass('notification-pulse');
    },

    /**
     * Bind listeners to the omnichannel-console layout
     *
     * @param {Layout} console - Omnichannel Console layout
     * @private
     */
    _bindConsoleListeners: function(console) {
        console.on('omnichannel:message', this._notifyUser, this);
        console.on('omniconsole:open', this._clearNotifications, this);
        console.context.on('omnichannel:auth', function(status) {
            this.setStatus(status);
        }, this);
    }
})
