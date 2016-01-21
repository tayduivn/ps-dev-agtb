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
     * What type of a config we are currently viewing.
     * default - is admin only available. Allows to configure default system Notifications Center settings.
     * user - is for any user. Allows to configure User Notification Center preferences.
     */
    section: 'user',

    /**
     * Grab the current config mode.
     * @inheritdoc
     */
    initialize: function(options) {
        var section = options.context.get('section');
        if (section && section === 'default') {
            this.section = 'global';
        }
        this._super('initialize', [options]);
        this.model.set('configMode', this.section);
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
     * We load config data because of the extended model's URL, not 'ModuleName/config'.
     * When we upgrade to backbone > 0.9.10 hard-code binding of the url to models methods in config-header-buttons
     * will be eliminated and we api call to model.fetch/save etc.;
     * @inheritdoc
     */
    loadConfig: function(options) {
        var configSection = (this.section === 'global') ? '/global' : '',
            url = app.api.buildURL(this.module, 'config' + configSection),
            self = this;
        app.api.call('read', url, null, {
                success: function(data) {
                    _.each(data, function(val, key) { self.model.set(key, val); }, self);
                    self.model.replaceDefaultToActualValues();
                    self.model.setSelectedAddresses();
                    self.render();
                }
            }
        );
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
        if (this.section === 'user') {
            access = true;
        } else {
            access = (app.user.get('type') === 'admin');
        }
        return access;
    }
})
