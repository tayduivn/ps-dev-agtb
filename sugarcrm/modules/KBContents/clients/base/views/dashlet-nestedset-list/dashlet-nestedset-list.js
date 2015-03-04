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
     * Module name that provides an netedset data.
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
     * Lifetime for data cache in ms.
     * @property {Number}
     */
    cacheLifetime: 300000,

    /**
     * Flag which indicate, if we need to use saved states.
     * @property {Boolean}
     */
    useStates: true,

    /**
     * Value of extraModule.field.
     * @property {String}
     */
    currentFieldValue: null,

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
        this.extraModule = this.meta.extra_provider || {};
        if (this.context.get('module') === this.extraModule.module &&
            (this.context.get('action') === 'detail' || this.context.get('action') === 'edit')
        ) {
            this.useStates = false;
            this.changedCallback = _.bind(this.modelFieldChanged, this);
            this.savedCallback = _.bind(this.modelSaved, this);

            this.context.get('model').on('change:' + this.extraModule.field, this.modelFieldChanged, this);
            this.context.get('model').on('data:sync:complete', this.modelSaved, this);

            this.currentFieldValue = this.context.get('model').get(this.extraModule.field);
        }
    },

    /**
     * The view doesn't need standard handlers for data change because it use own events and handlers.
     *
     * @override.
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
        this._super('_render');
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
     * @param data {Object} Selected item.
     */
    openRecord: function(data) {
        switch (data.type) {
            case 'document':
                if (_.isEmpty(this.extraModule.module)) {
                    break;
                }
                var route = app.router.buildRoute(this.extraModule.module, data.id);
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
                if (self.disposed) {
                    return;
                }
                self.selectNode(self.context.get('model').id)
            });
        });
    },

    /**
     * Handle load state of tree.
     * Always returns true to process the code, which called the method.
     * @param {Object} data Data of loaded tree.
     * @return {Boolean} Always returns `true`.
     */
    stateLoaded: function(data) {
        var originalUseState = this.useStates,
            self = this;
        async.forEach(data.open, function(value, callback) {
            self.useStates = false;
            value.open = true;
            self.folderToggled(value, callback);
        }, function() {
            _.each(data.open, function(value) {
                self.openNode(value.id);
            });
            self.useStates = originalUseState;
        });
        return true;
    },

    /**
     * Handle toggle of tree folder.
     * Always returns true to process the code, which called the method.
     * @param {Object} data Toggled folder.
     * @param {Function} callback Async callback to use with async.js
     * @return {Boolean} Return `true` to continue execution, `false` otherwise..
     */
    folderToggled: function (data, callback) {
        var triggeredCallback = false,
            self = this;
        if (data.open) {
            var model = this.collection.getChild(data.id),
                items = [];
            if (model.id) {
                items = model.children.models;
                if (items.length !== 0) {
                    triggeredCallback = true;
                    async.forEach(items, function(item, c) {
                        self.loadAdditionalLeaf(item.id, c);
                    }, function() {
                        if (_.isFunction(callback)) {
                            callback.call();
                        }
                    });
                    return false;
                }
            }
        }
        if (triggeredCallback === false && _.isFunction(callback)) {
            callback.call();
        }
        if (this.useStates) {
            this.saveJSTreeState();
        }
        return true;
    },

    /**
     * Handle leaf click for tree.
     * @param {Object} data Clicked leaf.
     */
    leafClicked: function (data) {
        if (data.type !== 'folder') {
            return;
        }
        this.loadAdditionalLeaf(data.id);
    },

    /**
     * Load extra data for tree.
     * @param {String} id Id of a leaf to load data in.
     * @param {Function} callback Async callback to use with async.js
     */
    loadAdditionalLeaf: function(id, callback) {
        var collection = app.data.createBeanCollection(this.extraModule.module),
            self = this;
        if (!_.isUndefined(this.loadedLeafs[id]) && this.loadedLeafs[id].timestamp < Date.now() - this.cacheLifetime) {
            delete this.loadedLeafs[id];
        }
        if (_.isEmpty(this.extraModule)
            || id === undefined
            || _.isEmpty(this.extraModule.module)
            || _.isEmpty(this.extraModule.field)
            || !_.isUndefined(this.loadedLeafs[id])
        ) {
            if (!_.isUndefined(this.loadedLeafs[id])) {
                this.addLeafs(this.loadedLeafs[id].models, id);
            }
            if (_.isFunction(callback)) {
                callback.call();
            }
            return;
        }
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
        collection.filterDef[0]['status'] = {$in: ['published-in', 'published-ex']};
        collection.filterDef[0]['active_rev'] = {$equals: 1};
        collection.fetch({
            success: function(data) {
                self.addLeafs(data.models || [], id);
                if (_.isFunction(callback)) {
                    callback.call();
                }
            }
        });
    },

    /**
     * {@inheritDoc}
     *
     * Need additional check for tree leafs.
     *
     * @override
     */
    loadData: function(options) {
        if (!options || _.isUndefined(options.saveLeafs) || options.saveLeafs === false) {
            this.loadedLeafs = {};
        }

        if (options && options.complete) {
            this._render();
            options.complete();
        }
    },

    /**
     * Override behavior of JSTree plugin.
     * @param {Data,BeanCollection} collection synced collection.
     */
    onNestedSetSyncComplete: function(collection) {
        if (this.disposed || this.collection.root !== collection.root) {
            return;
        }
        this.layout.reloadDashlet({complete: function() {}, saveLeafs: true});
    },

    /**
     * Handle change of this.extraModule.field.
     * @param {Data.Bean} model Changed model.
     * @param {String} value Current field value of the field.
     */
    modelFieldChanged: function(model, value) {
        delete this.loadedLeafs[this.currentFieldValue];
        this.currentFieldValue = value;
    },

    /**
     * Handle save of context model.
     */
    modelSaved: function() {
        delete this.loadedLeafs[this.currentFieldValue];
        this.onNestedSetSyncComplete(this.collection);
    },

    /**
     * {@inheritDoc}
     */
    _dispose: function() {
        if (this.useStates === false) {
            this.context.get('model').off('change:' + this.extraModule.field, this.changedCallback);
            this.context.get('model').off('data:sync:complete', this.savedCallback);
        }
        this._super('_dispose');
    },

    /**
     * Add documents as leafs for categories.
     * @param {Array} models Documents which should be added into the tree.
     * @param {String} id ID of category leaf to insert documents in.
     */
    addLeafs: function(models, id) {
        this.removeChildrens(id, 'document');
        if (models.length !== 0) {
            this.hideChildNodes(id);
            _.each(models, function(value) {
                var insData = {
                    id: value.id,
                    name: value.get('name')
                };
                this.insertNode(insData, id, 'document');
            }, this);
            this.showChildNodes(id);
        }
        this.loadedLeafs[id] = {
            timestamp: Date.now(),
            models: models
        };
    }
})
