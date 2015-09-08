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
 * @class View.Views.Base.NotificationCenterConfigCarriersView
 * @alias SUGAR.App.view.layouts.BaseNotificationCenterConfigCarriersView
 * @extends View.Views.Base.NotificationCenterConfigPanelView
 */
({
    extendsFrom: 'NotificationCenterConfigPanelView',

    /**
     * @inheritdoc
     */
    _getPanelDescription: function() {
        var description = (this.model.get('configMode') === 'admin') ?
            'LBL_CARRIERS_CONFIG_ADMIN_DESC' :
            'LBL_CARRIERS_CONFIG_USER_DESC';

        return app.lang.get(description, this.module);
    }
})
