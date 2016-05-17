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
 * @class View.Layouts.Base.CalDavConfigDrawerLayout
 * @alias SUGAR.App.view.layouts.BaseCalDavConfigDrawerLayout
 * @extends View.Layouts.Base.ConfigDrawerLayout
 */
({
    extendsFrom: 'ConfigDrawerLayout',

    /**
     * @inheritdoc
     *
     * read config data from RESTAPI and add to model
     *
     * @override
     */
    loadConfig: function(options) {
        var section = this.context.get('section');
        var url = app.api.buildURL('caldav', 'config'+(section ? '/'+section : ''), null, options.params);
        app.api.call('READ', url, options.attributes, {
            success: _.bind(function (data) {
                this.model.set('caldav_module_options', data.modules, {silent: true});
                this.model.set('caldav_module', data.values.caldav_module, {silent: true});
                this.model.set('caldav_interval_options', data.intervals, {silent: true});
                this.model.set('caldav_interval', data.values.caldav_interval, {silent: true});
                this.model.set('caldav_call_direction', data.values.caldav_call_direction, {silent: true});
                this.model.set('caldav_call_direction_options', data.call_directions, {silent: true});
                this.model.set('has_caldav_modules', true);
                this.render();
            }, this)
        });
    },

    /**
     * @inheritdoc
     *
     * No module No bean, there is no data in the meta. Turning off the check in Metadata
     *
     * @override
     */
    _checkConfigMetadata: function() {
        return true;
    },

    /**
     * Checks if the User has access to the current module
     *
     * @returns {boolean}
     * @private
     */
    _checkUserAccess: function() {
        var section = this.context.get('section');

        if (section == 'user') {
            return true;
        } else {
            return (app.user.get('type') == 'admin');
        }
    },

    /**
     * @inheritdoc
     */
    render: function(options) {
        var calDavModuleOptions = this.model.get('caldav_module_options');
        var section = this.context.get('section');

        if (calDavModuleOptions !== undefined) {
            if (section == 'user' && !_.size(calDavModuleOptions)) {
                var main = this.getComponent('sidebar').getComponent('main-pane');
                if (main) {
                    main.getComponent('config-header-buttons').meta.buttons = {};
                }
                this.model.set('has_caldav_modules', false);
            }
            this._super('render', [options]);
        }
    }
})
