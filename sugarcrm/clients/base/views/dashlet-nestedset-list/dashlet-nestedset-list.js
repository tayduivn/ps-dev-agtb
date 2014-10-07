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

    plugins: ['Dashlet', 'NestedSetCollection'],

    /**
     * List of tree nodes.
     *
     * @property {Object}
     */
    nodes: null,

    /**
     * ID of tree item which is in active state (currently selected).
     *
     * @property {String}
     */
    active: null,

    /**
     * Module name that provides an netedset data
     *
     * @property {String}
     */
    dataProvider: null,

    /**
     * Dashlet configuration parameters
     *
     * @property {Object}
     */
    config: {},

    /**
     * Initialize dashlet properties.
     */
    initDashlet: function() {
        this.dataProvider = this.settings.get('data_provider');
        this.config = app.metadata.getModule(
            this.settings.get('config_provider'),
            'config'
        );
        this.root = !_.isUndefined(this.config.category_root) ?
            this.config.category_root :
            null;
    },

    /**
     * {@inheritDoc}
     */
    bindDataChange: function() {
        if (this.model) {
            this.model.on('change', this.loadData, this);
        }
    },

    /**
     * {@inheritDoc}
     */
    loadData: function(options) {
        if (this.disposed || !this.dataProvider || !this.root) {
            return;
        }

        this.collection.tree({
            success: _.bind(this.render, this)
        });
    },

    /**
     * {@inheritDoc}
     */
    _render: function() {
        this._super('_render');
    }
})
