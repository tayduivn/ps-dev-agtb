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

    plugins: ['Dashlet', 'NestedSetCollection', 'JSTree'],


    /**
     * Module name that provides an netedset data
     *
     * @property {String}
     */
    moduleRoot: null,

    /**
     * Root ID of a shown NestedSet.
     * @property {String}
     */
    categoryRoot: null,

    /**
     * Initialize dashlet properties.
     */
    initDashlet: function() {
        var config = app.metadata.getModule(
            this.meta.config_provider,
            'config'
        );
        this.moduleRoot = this.settings.get('data_provider');
        this.categoryRoot = !_.isUndefined(config.category_root) ?
            config.category_root :
            null;
    },

    /**
     * {@inheritDoc}
     */
    bindDataChange: function() {},

    /**
     * {@inheritDoc}
     */
    _render: function() {
        var treeOptions = {
            category_root: this.categoryRoot,
            module_root: this.moduleRoot
        };
        this._super('_render', []);
        this._renderTree($('[data-place=dashlet-tree]'), treeOptions, {});
    }
})
