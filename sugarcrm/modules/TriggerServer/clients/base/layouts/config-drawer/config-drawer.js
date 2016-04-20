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
 * @class View.Layouts.Base.TriggerServerConfigDrawerLayout
 * @alias SUGAR.App.view.layouts.BaseTriggerServerConfigDrawerLayout
 * @extends View.Layouts.Base.ConfigDrawerLayout
 */
({
    extendsFrom: 'ConfigDrawerLayout',

    /**
     * Continues initializing Config and loads data.
     *
     * @param {Object} [options] The `options` param passed in to initialize
     */
    loadConfig: function (options) {
        //model.fetch get API call to url without 'config', for that reason we have to ge APi call
        var url = app.api.buildURL(this.module, 'config', null, options.params);
        app.api.call('READ', url, options.attributes, {
            success: _.bind(function (data) {
                this.model.set('triggerserver_protocol', data.triggerserver_protocol);
                this.model.set('triggerserver_host', data.triggerserver_host);
                this.model.set('triggerserver_port', data.triggerserver_port);
                this.render();
            }, this)
        });
    },

    /**
     * @inheritdoc
     * No module No bean, there is no data in the meta. Turning off the check in Metadata.
     *
     * @override
     */
    _checkConfigMetadata: function () {
        return true;
    },

    /**
     * Checks if the User has access to the current module.
     *
     * @returns {boolean}
     * @private
     */
    _checkUserAccess: function () {
        return (app.user.get('type') == 'admin');
    }
})
