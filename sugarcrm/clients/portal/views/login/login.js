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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * Login form view.
 *
 * @class View.Views.LoginView
 * @alias SUGAR.App.view.views.PortalLoginView
 */
({
    /**
     * @inheritDoc
     */
    plugins: ['ErrorDecoration', 'Tooltip'],

    /**
     * @inheritDoc
     */
    events: {
        'click [name=login_button]': 'login',
        'click [name=signup_button]': 'signup',
        'keypress': 'handleKeypress'
    },

    /**
     * @inheritDoc
     *
     * Hide `forgot password` tooltip when clicking anywhere outside the link.
     */
    initialize: function(options) {
        var self = this;

        this._super('initialize', [options]);

        $(document).on('click.login', function(event) {
            var $forgotPassword = self.$('#forgot-password'),
                forgotPassword = $forgotPassword.get(0);
            if (!$.contains(forgotPassword, event.target)) {
                app.utils.tooltip.hide(forgotPassword);
            }
        });
    },

    /**
     * Navigate to the `Signup` view.
     */
    signup: function() {
        app.router.navigate('#signup');
        app.router.start();
    },

    /**
     * @inheritDoc
     */
    postLogin: function() {
        app.$contentEl.show();
    },

    /**
     * @inheritDoc
     *
     * Remove event handler for hiding `forgot password` tooltip.
     */
    _dispose: function() {
        $(document).off('click.login');
        this._super('_dispose');
    }
})
