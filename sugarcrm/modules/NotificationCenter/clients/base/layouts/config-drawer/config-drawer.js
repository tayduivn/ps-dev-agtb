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
 * @class View.Layouts.Base.NotificationCenterConfigDrawerLayout
 * @alias SUGAR.App.view.layouts.BaseNotificationCenterConfigDrawerLayout
 * @extends View.Layouts.Base.ConfigDrawerLayout
 */
({
    extendsFrom: 'ConfigDrawerLayout',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        var section = options.context.get('section');
        if (section && section === 'user') {
            this.model.set('configMode', 'user');
        } else {
            this.model.set('configMode', 'admin');
        }
    },

    /**
     * Notification Center does not store its configuration in system 'config' table,
     * thus 'config' in app.metadata is not created for it. But we know, that this module is configurable.
     * @inheritdoc
     * @override
     */
    _checkConfigMetadata: function() {
        return true;
    },

    /**
     * This module has no Bean and thus no ACLs.
     * But it's allowed to be accessed by any user, with only one caveat:
     * only admin-user can obtain access to the global configuration of Notification Center.
     *
     * @inheritdoc
     */
    _checkUserAccess: function() {
        var access = false;
        if (this.model.get('configMode') === 'user') {
            access = true;
        } else {
            access = (app.user.get('type') === 'admin');
        }
        return access;
    }
})
