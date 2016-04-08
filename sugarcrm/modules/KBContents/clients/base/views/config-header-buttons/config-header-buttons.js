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
 * @class View.Views.Base.SchedulersJobsConfigHeaderButtonsView
 * @alias SUGAR.App.view.layouts.BaseSchedulersJobsConfigHeaderButtonsView
 * @extends View.Views.Base.ConfigHeaderButtonsView
 */
({
    extendsFrom: 'ConfigHeaderButtonsView',

    /**
     * Fix for RS-1284.
     * When we save config, metadata for current user is cleared.
     * That's why we need to sync metadata manually.
     * Otherwise user will see error message "Metadata is out of sync" when will try to create new KB content.
     *
     * @private
     */
    _saveConfig: function() {
        var self = this,
            url = app.api.buildURL(this.module, 'config'),
            model = this.context.get('model'),
            params = {
                success: _.bind(function(model) {
                    app.metadata.sync(function() {
                        self.showSavedConfirmation();
                        if (self.context.parent && self.context.parent.get('module') === self.module) {
                            self.context.parent.reloadData();
                        }
                        if (app.drawer && app.drawer.count()) {
                            app.drawer.close(self.context, self.context.get('model'));
                        } else {
                            app.router.navigate(self.module, {trigger: true});
                        }
                    });
                }, this),

                error: _.bind(function() {
                    self.getField('save_button').setDisabled(false);
                }, this)
            };

        // Standard ConfigHeaderButtonsView doesn't use doValidate
        model.doValidate(null, function(isValid){
            if (isValid) {
                app.api.call('create', url, model.attributes, params);
            } else {
                params.error();
            }
        });
    }
})
