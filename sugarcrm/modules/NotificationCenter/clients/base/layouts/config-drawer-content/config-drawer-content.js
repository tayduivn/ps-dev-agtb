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
 * @class View.Layouts.Base.NotificationCenterConfigDrawerContentLayout
 * @alias SUGAR.App.view.layouts.BaseNotificationCenterConfigDrawerContentLayout
 * @extends View.Layouts.Base.ConfigDrawerContentLayout
 */
({
    extendsFrom: 'ConfigDrawerContentLayout',

    /**
     * Get all config data as soon as possible, so that other views can obtain it right from the start.
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        var self = this;
        this.model.fetch({
            success: function(model) {
                self.render();
            }
        });
    },

    /**
     * @inheritdoc
     */
    _switchHowToData: function(helpId) {
        var title, text,
            module = this.module;

        switch(helpId) {
            case 'config-carriers':
                title = 'LBL_CARRIERS_CONFIG_TITLE';
                text = 'LBL_CARRIERS_CONFIG_HELP';
                break;

            case 'config-module':
                title = 'LBL_MODULE_CONFIG_TITLE';
                text = 'LBL_MODULE_CONFIG_HELP';
                break;
        }

        this.currentHowToData.title = app.lang.get(title, module);
        this.currentHowToData.text = app.lang.get(text, module);
    }
})
