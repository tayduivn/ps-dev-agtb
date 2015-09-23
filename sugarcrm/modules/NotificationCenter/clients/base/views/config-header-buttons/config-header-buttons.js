/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Views.Base.NotificationCenter.ConfigHeaderButtonsView
 * @alias SUGAR.App.view.views.BaseNotificationCenterConfigHeaderButtonsView
 * @extends  View.View
 */
({
    extendsFrom: 'ConfigHeaderButtonsView',

    /**
     * This method does the same thing as parent's one, but additionally on success redirects browser back,
     * thus preventing user to stay on Notification Center List View.
     * @inheritdoc
     */
    _saveConfig: function() {
        this.context.get('model').save({}, {
            success: _.bind(function(model) {
                if (app.drawer) {
                    this.showSavedConfirmation();
                    // close the drawer and return to Forecasts
                    app.drawer.close(this.context, this.context.get('model'));
                    app.router.goBack();
                }
            }, this),
            error: _.bind(function() {
                this.getField('save_button').setDisabled(false);
            }, this)
        });
    }
})
