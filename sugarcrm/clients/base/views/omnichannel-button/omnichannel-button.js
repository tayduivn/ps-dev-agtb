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
        }
    },

    /**
     * Sets button status.
     *
     * @param {string} Status string: logged-out, logged-in, active-session
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
            var console = app.view.createLayout({
                type: 'omnichannel-console'
            });
            console.initComponents();
            console.loadData();
            console.$el.hide();
            console.render();
            $('#sidecar').append(console.$el);
            app.omniConsole = console;
        }
        return app.omniConsole;
    },
})
