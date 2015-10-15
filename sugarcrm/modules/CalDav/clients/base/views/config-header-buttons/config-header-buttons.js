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
 * @class View.Views.Base.CalDavConfigHeaderButtonsView
 * @alias SUGAR.App.view.views.BaseCalDavConfigHeaderButtonsView
 * @extends View.Views.Base.ConfigHeaderButtonsView
 */
({
    extendsFrom: "ConfigHeaderButtonsView",

    events: {
        "click [name=save_button]":   "_save",
        "click [name=cancel_button]": "_cancel"
    },

    /**
     * Save the drawer.
     *
     * @private
     */
    _save: function() {
        app.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
        var value = {};
        value.caldav_module = this.model.get('caldav_module');
        value.caldav_interval = this.model.get('caldav_interval');
        if (value.caldav_module == 'Calls') {
            value.caldav_call_direction = this.model.get('caldav_call_direction');
        }
        var section = this.context.get('section');
        var url = app.api.buildURL('caldav', 'config/' + (section ? '/' + section : ''), null, null);
        app.api.call('update', url, value,{
            success: function() {
                    app.alert.dismiss('upload');
                    app.router.goBack();
            },
            error: function(error) {
                this.getField('save_button').setDisabled(false);
            }
        });
    },

    /**
     * Close the drawer.
     *
     * @private
     */
    _cancel: function() {
        app.router.goBack();
    }

})
