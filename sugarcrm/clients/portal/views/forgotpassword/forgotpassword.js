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
 * Portal Forgot Password form view.
 *
 * @class View.Views.Portal.ForgotpasswordView
 * @alias SUGAR.App.view.views.PortalForgotpasswordView
 * @extends View.View
 */
({
    plugins: ['ErrorDecoration'],

    events: {
        'click [name=forgotPassword_button]': 'forgotPassword'
    },

    /**
     * Gets the logo image for portal
     * @return string URL for the logo image for Portal
     */
    getLogoImage: function() {
        return app.config.logoURL || app.config.logomarkURL || app.metadata.getLogoUrl();
    },

    /**
     * Because we don't want any of the extra crap that stops it from rendering
     * @private
     */
    _render: function() {
        this.logoUrl = this.getLogoImage();
        app.view.View.prototype._render.call(this);
    },

    /**
     * Redirect to reset password confirmation page
     */
    forgotPassword: function() {
        var self = this;
        this.model.doValidate(this.getFields(null, this.model), function(isValid) {
            if (isValid) {
                var url = app.api.buildURL(
                    'password/resetemail',
                    '',
                    {},
                    {
                        platform: 'portal', username: self.model.get('username')
                    });
                app.api.call('read', url, null, {
                    success: function() {
                        app.router.navigate('#resetpwdconfirmation', {trigger: true});
                    },
                    error: function(error) {
                        app.alert.show('reset-email-fail', {
                            level: 'error',
                            title: app.lang.get(error.message),
                            autoClose: false
                        });
                    }
                });
            }
        });
    }
});
