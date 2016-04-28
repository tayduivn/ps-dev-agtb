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
 * @class View.Views.Base.WebSocketsConfigHeaderButtonsView
 * @alias SUGAR.App.view.views.BaseWebSocketsConfigHeaderButtonsView
 * @extends View.Views.Base.ConfigHeaderButtonsView
 */
({
    extendsFrom: "ConfigHeaderButtonsView",

    /**
     * Calls the context model save and saves the config model.
     *
     * @private
     */
    _saveConfig: function () {
        app.alert.dismiss('websockets_confirmation');

        var model = this.context.get('model');

        model.isNew = function() {
            return false;
        };
        var url = app.api.buildURL(this.module, 'config');
        app.api.call('update', url, model.attributes, {
            // getting the fresh model with correct config settings passed in as the param
            success: _.bind(function() {
                app.events.trigger('app:notifications:socket:config:changed');
                // If we're inside a drawer - refresh
                if (app.drawer) {
                    this.showSavedConfirmation();
                    app.drawer.close(this.context, this.context.get('model'));
                    //Reload metadata
                    app.sync();
                }
            }, this),
            error: _.bind(function(error) {
                this.getField('save_button').setDisabled(false);
                app.alert.show('websockets_confirmation', {
                    level: 'error',
                    autoClose: false,
                    messages: error.message
                });
            }, this)
        });
    }
})
