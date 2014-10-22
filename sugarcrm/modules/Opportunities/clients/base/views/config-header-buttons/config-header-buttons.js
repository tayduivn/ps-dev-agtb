// FILE SUGARCRM flav=ent ONLY
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
 * @class View.Views.Base.OpportunitiesConfigHeaderButtonsView
 * @alias SUGAR.App.view.views.BaseOpportunitiesConfigHeaderButtonsView
 * @extends View.Views.Base.ConfigHeaderButtonsView
 */
({
    extendsFrom: 'ConfigHeaderButtonsView',

    /**
     * Before the save triggers, we need to show the alert so the users know it's doing something.
     * @private
     */
    _beforeSaveConfig: function() {
        app.alert.show('opp.config.save', {level: 'process', title: app.lang.getAppString('LBL_SAVING')});
    },

    /**
     * @inheritDoc
     * @param {function} onClose
     */
    showSavedConfirmation: function(onClose) {
        app.alert.dismiss('opp.config.save');
        this._super('showSavedConfirmation', [onClose]);
    }
})
