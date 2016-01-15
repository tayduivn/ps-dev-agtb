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
 * @class View.Views.Base.NotificationCenter.NotificationCenterConfigHeaderButtonsView
 * @alias SUGAR.App.view.views.BaseNotificationCenterConfigHeaderButtonsView
 * @extends View.Views.Base.ConfigHeaderButtonsView
 */
({
    extendsFrom: 'ConfigHeaderButtonsView',

    initialize: function(options) {
        this.events = _.extend({}, this.events, {
            'click a[name=reset_all_button]': 'resetConfig'
        });
        this._super('initialize', [options]);
    },

    /**
     * Hide 'Reset' button if we are in default config mode.
     * @inheritdoc
     */
    render: function() {
        this._super('render');
        if (this.model.get('configMode') !== 'user') {
            this.getField('reset_all_button').hide();
        }
    },

    /**
     * Because we're not drawer we simply need to go back after save.
     * @inheritdoc
     */
    _saveConfig: function() {
        var configSection = (this.model.get('configMode') === 'global') ? '/global' : '';
        var url = app.api.buildURL(this.module, 'config' + configSection);
        this.model.updateCarriersAddresses();
        app.api.call('update', url, this.model.attributes, {
                success: _.bind(function() {
                    this.showSavedConfirmation();
                    app.router.goBack();
                }, this),
                error: _.bind(function() {
                    this.getField('save_button').setDisabled(false);
                }, this)
            }
        );
    },

    /**
     * Resetting model do default application state.
     */
    resetConfig: function() {
        var successMsg = app.lang.get('LBL_RESET_SETTINGS_SUCCESS', this.module);
        app.alert.show('reset_all_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('LBL_RESET_SETTINGS_ALL_CONFIRMATION', this.module),
            onConfirm: _.bind(function() {
                if (this.model.resetToDefault('all')) {
                    app.alert.show('reset_all_success', {
                        level: 'success',
                        autoClose: true,
                        messages: successMsg
                    });
                }
            }, this)
        });
    },

    /**
     * Because we're not drawer we simply need to go back.
     * @inheritdoc
     */
    cancelConfig: function() {
        if (this.triggerBefore('cancel')) {
            app.router.goBack();
        }
    }
})
