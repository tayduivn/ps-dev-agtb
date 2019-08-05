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
 * Portal Reset Password Confirmation view.
 *
 * @class View.Views.Portal.ResetPwdConfirmationView
 * @alias SUGAR.App.view.views.PortalResetPwdConfirmationView
 * @extends View.View
 */
({
    /**
     * Gets the logo image for portal
     */
    getLogoImage: function() {
        // get the image urls for portal
        return app.config.logoURL || app.config.logomarkURL || app.metadata.getLogoUrl();
    },

    /**
     * Because we don't want any of the extra crap that stops it from rendering
     * @private
     */
    _render: function() {
        this.logoUrl = this.getLogoImage();
        app.view.View.prototype._render.call(this);
    }
});
