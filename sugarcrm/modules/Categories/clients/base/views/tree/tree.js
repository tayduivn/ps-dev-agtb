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
({
    plugins: ['JSTree', 'NestedSetCollection'],

    /**
     * Default settings. 
     */
    _defaultSettings: {
        showMenu: true
    },

    /**
     * Aggregated settings.
     */
    _settings: {},

    /**
     * Initialize _settings object.
     * @return {Object}
     * @private
     */
    _initSettings: function() {
        this._settings = _.extend({},
            this._defaultSettings,
            this.context.get('treeoptions') || {},
            this.def && this.def.settings || {}
        );
        return this;
    },

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this._initSettings();
    }
})
