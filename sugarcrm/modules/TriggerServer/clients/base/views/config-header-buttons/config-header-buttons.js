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
 * @class View.Views.Base.TriggerServerConfigHeaderButtonsView
 * @alias SUGAR.App.view.views.BaseTriggerServerConfigHeaderButtonsView
 * @extends View.Views.Base.ConfigHeaderButtonsView
 */
({
    extendsFrom: "ConfigHeaderButtonsView",

    /**
     * Calls the context model save and saves the config model.
     *
     * @override
     * @private
     */
    _saveConfig: function () {
        app.alert.dismiss('trigger_server_confirmation');

        this.context.get('model').save({}, {
            // getting the fresh model with correct config settings passed in as the param
            success: _.bind(function (model) {
                // If we're inside a drawer - refresh
                if (app.drawer) {
                    this.showSavedConfirmation();
                    app.drawer.close(this.context, this.context.get('model'));
                    //Reload metadata
                    app.sync();
                }
            }, this),
            error: _.bind(function (error) {
                this.getField('save_button').setDisabled(false);
                app.alert.show('trigger_server_confirmation', {
                    level: 'error',
                    autoClose: false,
                    messages: error.message
                });
            }, this)
        });
    }
})
