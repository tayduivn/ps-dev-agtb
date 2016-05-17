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
({
    extendsFrom: 'ConfigPanelView',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.userName = app.user.get('user_name');

        var parser = document.createElement('a');
        parser.href = app.config.siteUrl;

        this.isHasCalDavModules = true;
        this.serverAddress = parser.hostname;
        this.serverPath = parser.pathname || '/';
        if (this.serverPath.slice(-1) !== '/') {
            this.serverPath += '/';
        }
        if (parser.port) {
            this.serverPort = parser.port;
        } else if (parser.protocol === 'https:') {
            this.serverPort = 443;
        } else {
            this.serverPort = 80;
        }

        _.each(this.meta.panels, function(panel) {
            _.each(panel.fields, function(field, fieldKey) {
                if (this.context.get('section') !== 'user') {
                    panel.fields[fieldKey].description += '_ADMIN';
                }
            }, this);
        }, this);
    },

    /**
     * @inheritdoc
     */
    render: function(options) {
        this.isHasCalDavModules = this.model.get('has_caldav_modules');
        this._super('render', [options]);
    }
})
