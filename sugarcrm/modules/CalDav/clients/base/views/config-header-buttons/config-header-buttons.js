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
 * @class View.Views.Base.CalDavConfigHeaderButtonsView
 * @alias SUGAR.App.view.views.BaseCalDavConfigHeaderButtonsView
 * @extends View.Views.Base.ConfigHeaderButtonsView
 */
({
    extendsFrom: "ConfigHeaderButtonsView",

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.titleLabel = (this.context.get('section') !== 'user') ?
            'LBL_CONFIG_TITLE_MODULE_SETTINGS_ADMIN' :
            'LBL_CONFIG_TITLE_MODULE_SETTINGS';
    },

    /**
     * @inheritdoc
     */
    saveConfig: function() {
        var self = this;
        if (this.triggerBefore('save')) {
            this.getField('save_button').setDisabled(true);
            this.model.doValidate(null, function(isValid) {
                if (isValid) {
                    self._saveConfig();
                } else {
                    self.getField('save_button').setDisabled(false);
                }
            });
        }
    },

    /**
     * Save the drawer.
     *
     * @private
     */
    _saveConfig: function() {
        var value = {};
        if (this.context.get('section') !== 'user') {
            value.caldav_enable_sync = this.model.get('caldav_enable_sync');
        }
        value.caldav_module = this.model.get('caldav_module');
        value.caldav_interval = this.model.get('caldav_interval');
        if (value.caldav_module == 'Calls') {
            value.caldav_call_direction = this.model.get('caldav_call_direction');
        }
        var section = this.context.get('section');
        var url = app.api.buildURL('caldav', 'config' + (section ? '/' + section : ''), null, null);
        app.alert.show('savind_caldav_config', {
            level: 'process',
            title: app.lang.get('LBL_SAVING')
        });
        app.api.call('update', url, value,{
            success: _.bind(function() {
                this.showSavedConfirmation();
                app.alert.dismiss('savind_caldav_config');
                // close the drawer
                app.drawer.close(this.context, this.context.get('model'));
                //Reload metadata
                app.sync();
            }, this),
            error: _.bind(function() {
                this.getField('save_button').setDisabled(false);
            }, this)
        });
    }
})
