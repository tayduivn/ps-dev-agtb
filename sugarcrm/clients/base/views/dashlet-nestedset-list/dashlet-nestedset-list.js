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
     * Cache to store loaded leafs to prevent extra loading.
     * @property {Object}
     */
    loadedLeafs: null,

    /**
     * Lifetime for data cache.
     * @property {Number}
     */
    cacheLifetime: 300000,

    /**
     * Flag which indicate, if we need to use saved states.
     * @property {Boolean}
     */
    useStates: true,

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
        if (this.context.get('module') === this.extraModule.module && this.context.get('action') === 'detail') {
            this.useStates = false;
        }
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
            settings: {
                category_root: this.categoryRoot,
                module_root: this.moduleRoot,
                plugins: [],
                liHeight: 14
            },
            options: {
            }},
            callbacks = {
                onLeaf: _.bind(this.leafClicked, this),
                onToggle: _.bind(this.folderToggled, this),
                onLoad: _.bind(this.treeLoaded, this),
                onSelect: _.bind(this.openRecord, this),
                onLoadState:  _.bind(this.stateLoaded, this)
            };
        if (this.useStates === true) {
            treeOptions.settings.plugins.push('state');
            treeOptions.options.state = {
                save_selected: false,
                auto_save: false,
                save_opened: 'jstree_open',
                options: {},
                storage: this._getStorage()
            };
        }
        this._super('_render', []);
        this._renderTree($('[data-place=dashlet-tree]'), treeOptions, callbacks);
    },

    /**
     * Return storage for tree state.
     * @return {Function}
     * @private
     */
    _getStorage: function () {
        var self = this;
        return function(key, value, options) {
            var intKey = app.user.lastState.buildKey(self.categoryRoot, self.moduleRoot, self.module);
            if (!_.isUndefined(value)) {
                app.user.lastState.set(intKey, value);
            }
            return app.user.lastState.get(intKey);
        };
    },

    /**
     * Handle tree selection.
     * @param data
     */
    openRecord: function(data) {
        switch (data.type) {
            case 'document':
                if (_.isEmpty(this.extraModule.module)) {
                    break;
                }
                var route = app.router.buildRoute(this.extraModule.module, data.id, 'record');
                app.router.navigate(route, {trigger: true});
                break;
            case 'folder':
                break;
        }
    },

    /**
     * Handle tree loaded. Load additional leafs for the tree.
     * @return {Boolean} Always true.
     */
    treeLoaded: function() {
        var self = this;
        async.forEach(this.collection.models, function(model, callback) {
            self.loadAdditionalLeaf(model.id, callback);
        }, function() {
            if (self.useStates) {
                self.loadJSTreeState();
            } else {
                self.openCurrentParent();
            }
        });
        return true;
    },

    /**
     * Open category, which is parent to current record.
     */
    openCurrentParent: function() {
        if (_.isEmpty(this.extraModule)
            || _.isEmpty(this.extraModule.module)
            || _.isEmpty(this.extraModule.field)
            ) {
            return;
        }
        var id = this.context.get('model').get(this.extraModule.field),
            self = this;
        this.loadAdditionalLeaf(id, function() {
            _.defer(function() {
                self.selectNode(self.context.get('model').id)
            });
        });
    },

    /**
     * Handle load state of tree.
     * @param {Object} data
     */
    stateLoaded: function(data) {
        _.each(data.open, function(value) {
            value.open = true;
            this.folderToggled(value);
        }, this);
        return true;
    },

    /**
     * Handle toggle of tree folder.
     * @param {Object} data
     * @return {Boolean}
     */
    folderToggled: function (data) {
        if (data.open) {
            var model = this.collection.getChild(data.id),
                items = [];
            if (!model.id) {
                return true;
            }
            items = model.children.models;
            if (items.length === 0) {
                return true;
            }
            _.each(items, function(item) {
                this.loadAdditionalLeaf(item.id);
            }, this);
        }
        if (this.useStates) {
            this.saveJSTreeState();
        }
        return true;
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
     * @param {String} id
     * @param {Function} callback Async callback to use with async.js
     */
    loadAdditionalLeaf: function(id, callback) {
        if (!_.isUndefined(this.loadedLeafs[id]) && this.loadedLeafs[id] < Date.now() - this.cacheLifetime) {
            delete this.loadedLeafs[id];
        }
        if (_.isEmpty(this.extraModule)
            || id === undefined
            || _.isEmpty(this.extraModule.module)
            || _.isEmpty(this.extraModule.field)
            || !_.isUndefined(this.loadedLeafs[id])
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
        collection.filterDef[0]['status'] = {$in: ['published', 'published-in', 'published-ex']};
        collection.filterDef[0]['active_rev'] = {$equals: 1};
        collection.fetch({
            success: function(data) {
                self.removeChildrens(id, 'document');
                if (data.length !== 0) {
                    self.hideChildNodes(id);
                    _.each(data.models, function(value) {
                        var insData = {
                            id: value.id,
                            name: value.get('name')
                        };
                        this.insertNode(insData, id, 'document');
                    }, self);
                    self.showChildNodes(id);
                    self.loadedLeafs[id] = Date.now();
                }
                if (_.isFunction(callback)) {
                    callback.call();
                }
            }
        });
    },

    /**
     * {@inheritDoc}
     */
    loadData: function(options) {
        this.loadedLeafs = {};
        if (options && options.complete) {
            this._render();
            options.complete();
        }
    }
})
