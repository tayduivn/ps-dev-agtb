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
     * Module to load additional data into nested set.
     * @property {Object}
     * @property {String} extraModule.module Module to load additional data from.
     * @property {String} extraModule.field Linked field of provided module.
     */
    extraModule: null,

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
        this.extraModule = this.meta.extra_provider || null;
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
        },
            callbacks = {
                onLeaf: _.bind(this.leafClicked, this)
            };
        this._super('_render', []);
        this._renderTree($('[data-place=dashlet-tree]'), treeOptions, callbacks);
    },

    /**
     * Handle leaf click for tree.
     * @param {Object} data
     */
    leafClicked: function (data) {
        if (data.type !== 'folder') {
            return;
        }
        this.loadAdditionalLeaf(data.id);
    },

    /**
     * Load extra data for tree.
     * @param id
     */
    loadAdditionalLeaf: function(id) {
        if (_.isEmpty(this.extraModule)
            || id === undefined
            || _.isEmpty(this.extraModule.module)
            || _.isEmpty(this.extraModule.field)
        ) {
            return;
        }
        var collection = app.data.createBeanCollection(this.extraModule.module),
            self = this;
        collection.options = {
            params: {
                order_by: 'date_entered:desc'
            },
            fields: [
                'id',
                'name'
            ]
        };

        collection.filterDef = [{}];
        collection.filterDef[0][this.extraModule.field] = {$equals: id};
        collection.fetch({
            success: function(data) {
                _.each(data.models, function(value) {
                    var insData = {
                        id: value.id,
                        name: value.get('name')
                    };
                    this.insertNode(insData, id, 'document');
                }, self);
            }
        });
    },

    /**
     * {@inheritDoc}
     */
    loadData: function(options) {
        if (options && options.complete) {
            this._render();
            options.complete();
        }
    }
})
