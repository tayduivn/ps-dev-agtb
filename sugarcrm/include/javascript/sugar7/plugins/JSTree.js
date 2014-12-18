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
(function(app) {
    app.events.on('app:init', function() {
        app.plugins.register('JSTree', ['view', 'field'], {

            /**
             * The JS Tree Object.
             * @property {Object} jsTree
             */
            jsTree: null,

            /**
             * JSTree settings.
             * @property {Object} jsTreeSettings
             */
            jsTreeSettings: null,

            /**
             * JSTree callbacks.
             * @property {Object} jsTreeCallbacks
             */
            jsTreeCallbacks: null,

            /**
             * JSTree options
             * @property {Object}
             */
            jsTreeOptions: null,

            /**
             * JQuery container with empty label
             * @property {Object} $noData
             */
            $noData: null,

            /**
             * JQuery container with empty label
             * @property {Object} $treeContainer
             */
            $treeContainer: null,

            /**
             * {@inheritDoc}
             */
            onAttach: function(component, plugin) {
                this.on('init', function() {
                    app.events.on(
                        'app:nestedset:sync:complete',
                        this.onNestedSetSyncComplete,
                        this
                    );
                });
            },

            /**
             * @param {View.Component} component The component this plugin is attached to.
             */
            onDetach: function(component) {
                if (!_.isEmpty(this.jsTree)) {
                    this.jsTree.off();
                }
                app.events.off(
                    'app:nestedset:sync:complete',
                    this.onNestedSetSyncComplete,
                    this
                );
            },

            /**
             * Handler on sync NestedSetCollection.
             *
             * Re-render all nested set views that display same category root.
             * As additional action - refresh context menu.
             *
             * @param {Data.NestedSetCollection} collection Synced collection.
             */
            onNestedSetSyncComplete: function(collection) {
                if (_.isFunction(Object.getPrototypeOf(this).onNestedSetSyncComplete)) {
                    Object.getPrototypeOf(this).onNestedSetSyncComplete.call(this, collection);
                    return;
                }
                this._refreshContextMenu();
                if (this.disposed || _.isUndefined(this.collection) ||
                    this.collection.root !== collection.root
                ) {
                    return;
                }

                if (this.collection !== collection) {
                    this.render();
                }
            },

            /**
             * Recursively step through the tree and for each node representing a tree node,
             * run the data attribute through the replaceHTMLChars function.
             * This function supports n-levels of the tree hierarchy.
             *
             * @param {Object} data The data structure returned from the REST API.
             * @param {Object} ctx A reference to the view's context.
             * @return {Object} object The modified data structure.
             * @private
             */
            _recursiveReplaceHTMLChars: function(data, ctx) {

                _.each(data, function(entry, index) {

                    //Scan for the nodes with the data attribute.  These are the nodes we are interested in
                    if (entry.data) {
                        data[index].data = (function(value) {
                            return value
                                .replace(/&amp;/gi, '&')
                                .replace(/&lt;/gi, '<')
                                .replace(/&gt;/gi, '>')
                                .replace(/&#039;/gi, '\'')
                                .replace(/&quot;/gi, '"');
                        })(entry.data);

                        if (entry.children) {
                            //For each children found (if any) then call _recursiveReplaceHTMLChars again.
                            // childEntry to an Array.
                            // This is crucial so that the beginning _.each loop runs correctly.
                            _.each(entry.children, function(childEntry, index2) {
                                entry.children[index2] = ctx._recursiveReplaceHTMLChars([childEntry]);
                            }, this);
                        }
                    }
                }, this);

                return data;
            },

            /**
             * Render JSTree.
             * @param {Object} $container
             * @param {Object} settings
             * @param {String} settings.settings.module_root Module parameter to build a collection (required).
             * @param {String} settings.settings.category_root Root parameter to build a collection (required).
             * @param {Object} callbacks
             * @param {Object} callbacks.onToggle Callback on expand/collapse a tree branch.
             * @param {Object} callbacks.onLoad Callback on tree loaded.
             * @param {Object} callbacks.onLeaf Callback on leaf click.
             * @param {Object} callbacks.onShowContextmenu Callback on show a context menu.
             * @param {Object} callbacks.onAdd Callback on add a new node.
             * @param {Object} callbacks.onLoadState Callback on load state.
             * @param {Object} callbacks.onSelect Callback on select a node.
             * @private
             */
            _renderTree: function($container, settings, callbacks) {

                this.jsTreeSettings = settings.settings || {};
                this.jsTreeOptions = settings.options || {};
                this.jsTreeCallbacks = callbacks || {};

                this.$noData = $('<div />', {'data-type': 'jstree-no-data', class: 'block-footer'})
                    .html(app.lang.get('LBL_NO_DATA_AVAILABLE', this.module));
                this.$treeContainer = $('<div />', '');

                this.dataProvider = this.jsTreeSettings.module_root || null;
                this.root = this.jsTreeSettings.category_root || null;

                if (!this.dataProvider || !this.root) {
                    return;
                }
                $container.empty();
                $container.append(this.$noData).append(this.$treeContainer);
                this._toggleVisibility(true);
                this.collection.module = this.dataProvider;
                this.collection.root = this.root;
                this.collection.tree({
                    success: _.bind(function(data) {
                        this.createTree(data.jsonTree, this.$treeContainer, this.loadPluginsList());
                    }, this)
                });
            },

            /**
             * Hide tree if there is no data, show otherwise.
             * @param {Boolean} hide Hide tree if true, show otherwise.
             * @private
             */
            _toggleVisibility: function(hide) {
                if (hide === true) {
                    this.$treeContainer.hide();
                    this.$noData.show();
                } else {
                    this.$treeContainer.show();
                    this.$noData.hide();
                }
            },

            /**
             * Load JSTree plugins list.
             */
            loadPluginsList: function() {
                return _.union(
                    ['json_data', 'ui', 'crrm', 'types', 'themes', 'search'], // default plugins
                    !_.isUndefined(this.jsTreeSettings.plugins) ? this.jsTreeSettings.plugins : []
                );
            },

            /**
             * Create JSTree.
             * @param {Object} data
             * @param {Object} $container
             * @param {Array} plugins
             * @example List of available plugins, based on common jstree list.
             * ```
             * ['json_data', 'dnd', 'ui', 'crrm', 'types', 'themes', 'contextmenu', 'search']
             * ```
             */
            createTree: function(data, $container, plugins) {
                this._toggleVisibility(data.length === 0);
                // make sure we're using an array
                // if the data coming from the endpoint is an array with one element
                // it gets converted to a JS object in the process of getting here
                if (!_.isArray(data)) {
                    data = [data];
                }
                var treeData = this._recursiveReplaceHTMLChars(data, this),
                    fn = function(el) {
                        if (!_.isEmpty(el.children)) {
                            _.each(el.children.records, fn);
                            el.children = el.children.records;
                        }
                        el.data = el.name;
                        el.metadata = {id: el.id};
                        el.attr = {'data-id': el.id, 'data-level': el.level, 'id': el.id};
                    },
                    jsTreeOptions = {
                        core: {
                            html_titles: true
                        },
                        settings: this.jsTreeSettings,
                        plugins: _.isEmpty(plugins) ? this.loadPluginsList() : plugins,
                        json_data: {
                            'data': treeData
                        },
                        contextmenu: {
                            items: this._loadContextMenu(this.jsTreeSettings),
                            show_at_node: false
                        },
                        search: {
                            case_insensitive: true
                        }
                    };
                jsTreeOptions = _.extend({}, jsTreeOptions, this.jsTreeOptions);
                treeData.ctx = this.context;

                _.each(treeData, fn);

                this.jsTree = $container.jstree(jsTreeOptions)
                .on('loaded.jstree', _.bind(function() {
                    this._loadedHandler($container);
                }, this))
                .on('select_node.jstree', _.bind(this._selectNodeHandler, this))
                .on('create.jstree', _.bind(this._createHandler, this))
                .on('move_node.jstree', _.bind(this._moveHandler, this))
                .on('remove.jstree', _.bind(this._removeHandler, this))
                .on('rename_node.jstree', _.bind(this._renameHandler, this))
                .on('load_state.jstree', _.bind(this._loadedStateHandler, this))
                .on('search.jstree', _.bind(this._searchHandler, this));
            },

            /**
             * Drag-and-Drop handler when node move is finished.
             * @param {Event} event
             * @param {Object} data
             * @private
             */
            _moveHandler: function(event, data) {
                /**
                 * Catch Drag-And-Drop move_node
                 */
                if ($.vakata.dnd.is_drag && $.vakata.dnd.user_data.jstree) {
                    if (!_.isUndefined(data.rslt.o) && !_.isUndefined(data.rslt.r)) {
                        this.moveNode(data.rslt.o.data('id'), data.rslt.r.data('id'), data.rslt.p, function(obj, response) {
                            var levelDelta = parseInt(obj.level) - parseInt($(data.rslt.o).data('level'));
                            //set new level for dragged node
                            $(data.rslt.o).attr('data-level', obj.level);
                            $(data.rslt.o).data('level', obj.level);
                            //recalculate the level for all nodes within selected
                            _.each($(data.rslt.o).find('li'), function(item){
                                var currentLevel = parseInt($(item).attr('data-level'));
                                $(item).attr('data-level',  currentLevel + levelDelta);
                                $(item).data('level', currentLevel + levelDelta);
                            });
                        });
                    }
                }
            },

            /**
             * Hadle load state of tree.
             * @param {Event} event
             * @param {Object} data
             * @private
             */
            _loadedStateHandler: function (event, data) {
                if (this.jsTreeCallbacks.onLoadState) {
                    _.each(data.rslt, function(val, ind) {
                        _.each(val, function(v, i) {
                            var id = v,
                                node = this.jsTree.find('[data-id=' + id +']'),
                                selectedNode = {
                                    id: id,
                                    name: node.find('a:first').text().trim(),
                                    type: node.data('type') || 'folder'
                                };
                            val[i] = selectedNode;
                        }, this);
                        data.rslt[ind] = val;
                    }, this);
                    this.jsTreeCallbacks.onLoadState(data.rslt);
                }
            },

            /**
             * Remove node handler.
             * @param {Event} event
             * @param {Object} data
             * @private
             */
            _removeHandler: function(event, data) {
                /* ToDo: implement action on remove */
            },

            /**
             * Rename node handler.
             * @param {Event} event
             * @param {Object} data
             * @private
             */
            _renameHandler: function(event, data) {
                if (!_.isUndefined(data.rslt.obj.data('id'))) {
                    var bean = this.collection.getChild(data.rslt.obj.data('id'));
                    if (!_.isUndefined(bean)) {
                        if (bean.get('name') !== data.rslt.name) {
                            bean.set('name', data.rslt.name);
                            bean.save();
                        }
                    }
                }
            },

            /**
             * Search node handler.
             * @param {Event} event
             * @param {Object} data
             * @private
             */
            _searchHandler: function(event, data) {
                /* ToDo: handler for search node - wbi */
            },

            /**
             * Load context menu.
             * @param {Object} settings
             * @param {Boolean} settings.showMenu Show menu ot not.
             * @return {Object}
             * @private
             */
            _loadContextMenu: function(settings) {
                var self = this;
                if (settings.showMenu === true) {
                    return {
                        edit: {
                            separator_before: false,
                            separator_after: true,
                            _disabled: false,
                            label: app.lang.get('LBL_CONTEXTMENU_EDIT', self.module),
                            action: function(obj) {
                                this.rename(obj);
                            }
                        },
                        moveup: {
                            separator_before: false,
                            separator_after: true,
                            _disabled: false,
                            label: app.lang.get('LBL_CONTEXTMENU_MOVEUP', self.module),
                            action: function(obj) {
                                var currentNode = this._get_node(obj),
                                    prevNode = this._get_prev(obj, true);
                                if (currentNode && prevNode) {
                                    self.moveNode(
                                        currentNode.data('id'),
                                        prevNode.data('id'),
                                        'before',
                                        function() {
                                            $(currentNode).after($(prevNode));
                                        });
                                }
                            }
                        },
                        movedown: {
                            separator_before: false,
                            separator_after: true,
                            _disabled: false,
                            label: app.lang.get('LBL_CONTEXTMENU_MOVEDOWN', self.module),
                            action: function(obj) {
                                var currentNode = this._get_node(obj),
                                    nextNode = this._get_next(obj, true);
                                if (currentNode && nextNode) {
                                    self.moveNode(
                                        currentNode.data('id'),
                                        nextNode.data('id'),
                                        'after',
                                        function() {
                                            $(nextNode).after($(currentNode));
                                        });
                                }
                            }
                        },
                        moveto: {
                            separator_before: false,
                            separator_after: true,
                            _disabled: false,
                            label: app.lang.get('LBL_CONTEXTMENU_MOVETO', self.module),
                            action: false,
                            submenu: this._buildRootsSubmenu()
                        },
                        delete: {
                            separator_before: false,
                            separator_after: true,
                            _disabled: false,
                            label: app.lang.get('LBL_CONTEXTMENU_DELETE', self.module),
                            action: function(obj) {
                                var bean = self.collection.getChild(obj.data('id'));
                                if (!_.isUndefined(bean)) {
                                    self.warnDelete({
                                        model: bean,
                                        success: _.bind(function() {
                                            this.remove(this.is_selected(obj) ? obj : null);
                                            self._toggleVisibility(self.collection.length === 0);
                                        }, this)
                                    });
                                }
                            }
                        }
                    };
                } else {
                    return {};
                }
            },

            /**
             * Popup dialog message to confirm delete action.
             * @param {Object} options
             * @param {Data.Bean} options.model Model to delete.
             * @param {Function} options.success Calback on success.
             */
            warnDelete: function(options) {
                options = options || {};
                if (_.isEmpty(options.model)) {
                    return;
                }

                app.alert.show('delete_confirmation', {
                    level: 'confirmation',
                    messages: app.utils.formatString(
                        app.lang.get('NTC_DELETE_CONFIRMATION_FORMATTED'),
                        [app.lang.getModuleName(options.model.module).toLowerCase() +
                            ' ' + app.utils.getRecordName(options.model).trim()]
                    ),
                    onConfirm: _.bind(function() {
                        this.deleteModel(options);
                    }, this),
                    onCancel: function() {

                    }
                });
            },

            /**
             * Delete the model once the user confirms the action.
             * @param {Object} options
             * @param {Data.Bean} options.model Model to delete.
             * @param {Function} options.success Calback on success.
             */
            deleteModel: function(options) {
                options = options || {};
                options.success = options.success || null;
                if (_.isEmpty(options.model)) {
                    return;
                }

                options.model.destroy({
                    //Show alerts for this request
                    showAlerts: {
                        'process': true,
                        'success': {
                            messages: app.utils.formatString(
                                app.lang.get('NTC_DELETE_SUCCESS'),
                                [app.lang.getModuleName(options.model.module).toLowerCase() +
                                    ' ' + app.utils.getRecordName(options.model).trim()]
                            )
                        }
                    },
                    success: options.success
                });
            },

            /**
             * Build submenu from root items.
             * @return {Object}
             * @private
             */
            _buildRootsSubmenu: function() {
                var self = this,
                    subMenu = {};
                _.each(this.collection.models, function(entry, index) {
                    subMenu['movetosubmenu' + index] = {
                        id: entry.id,
                        separator_before: false,
                        icon: 'jstree-icon',
                        separator_after: false,
                        label: entry.get('name'),
                        action: function(obj) {
                            self.moveNode(obj.data('id'), entry.id, 'last', function(data, response) {
                                self.jsTree.jstree(
                                    'move_node',
                                    self.jsTree.jstree('get_instance')
                                        .get_container_ul()
                                        .find('li[data-id=' + obj.data('id') + ']'),
                                    self.jsTree.jstree('get_instance')
                                        .get_container_ul()
                                        .find('li[data-id=' + entry.id + ']')
                                );
                            });
                        }
                    };
                });
                return subMenu;
            },

            /**
             * Handle actions when tree is loaded.
             * @param {Object} $container
             * @private
             */
            _loadedHandler: function($container) {
                $container
                    .addClass('jstree-sugar')
                    .addClass('tree-component');
                if (this.jsTreeCallbacks.onLoad) {
                    this.jsTreeCallbacks.onLoad.apply();
                }
            },

            /**
             * Handle actions when node is selected.
             * @param {Event} event
             * @param {Object} data
             * @return {boolean}
             * @private
             */
            _selectNodeHandler: function(event, data) {
                if (!_.isUndefined(data.args[0])) {
                    var selectedNode = {
                        id: data.rslt.obj.data('id'),
                        name: data.rslt.obj.find('a:first').text().trim(),
                        type: data.rslt.obj.data('type') || 'folder'
                        },
                        action = $(data.args[0]).data('action');
                    if (action === 'jstree-toggle' && data.rslt.obj.hasClass('jstree-leaf')) {
                        action = 'jstree-leaf-click';
                    }
                    switch (action) {
                        case 'jstree-toggle':
                            selectedNode.open = data.rslt.obj.hasClass('jstree-closed') ? true : false;
                            if (this.jsTreeCallbacks.onToggle &&
                                !this.jsTreeCallbacks.onToggle.apply(this, [selectedNode])) {
                                return false;
                            }
                            this._jstreeToggle(event, data);
                            break;
                        case 'jstree-leaf-click':
                            if (this.jsTreeCallbacks.onLeaf &&
                                !this.jsTreeCallbacks.onLeaf.apply(this, [selectedNode])) {
                                return false;
                            }
                            break;
                        case 'jstree-contextmenu':
                            if (this.jsTreeCallbacks.onShowContextmenu &&
                                !this.jsTreeCallbacks.onShowContextmenu.apply(this, [event, data])) {
                                return false;
                            }
                            this._jstreeShowContextmenu(event, data);
                            break;
                        case 'jstree-addnode':
                            if (this.jsTreeCallbacks.onAdd &&
                                !this.jsTreeCallbacks.onAdd.apply(this, [event, data])) {
                                return false;
                            }
                            this._onAdd(event, data);
                            break;
                        case 'jstree-select':
                            if (this.jsTreeCallbacks.onSelect &&
                                !this.jsTreeCallbacks.onSelect.apply(this, [selectedNode])) {
                                return false;
                            }
                            this._jstreeSelectNode(selectedNode);
                            break;
                    }
                }
            },

            /**
             * Handle actions when node is created.
             * @param {Event} event
             * @param {Object} data
             * @private
             */
            _createHandler: function(event, data) {
                var parentId = data.rslt.parent === -1 ? this.root : data.rslt.parent.data('id'),
                    newNode = data.rslt.obj,
                    node = {
                        title: data.rslt.name,
                        position: data.rslt.position
                    },
                    self = this;

                if (data.args[2] === undefined || data.args[2].id === undefined) {
                    this.collection.append({
                        target: parentId,
                        data: {name: node.title},
                        success: function(item) {
                            newNode.attr('data-id', item.id);
                            newNode.attr('data-level', item.level);
                            self._toggleVisibility(false);
                        },
                        error: function() {
                        // ToDo: remove node - will be implemented
                        }
                    });
                }
            },

            /**
             * Toggle tree node.
             * @param {Event} event
             * @param {Object} data
             * @private
             */
            _jstreeToggle: function(event, data) {
                data.inst.toggle_node(data.rslt.obj);
            },

            /**
             * Show Context menu.
             * @param {Event} event
             * @param {Object} data
             * @private
             */
            _jstreeShowContextmenu: function(event, data) {
                var container = data.inst._get_node().parent(),
                    level = data.inst._get_node().attr('data-level'),
                    firstNodeId = $(container).find('li[data-level=' + level + ']').first().data('id'),
                    lastNodeId = $(container).find('li[data-level=' + level + ']').last().data('id');

                if (!_.isUndefined(data.inst.get_settings().contextmenu.items)) {
                    data.inst._set_settings({
                        contextmenu: {
                            items: {
                                moveup: {_disabled: data.inst._get_node().data('id') === firstNodeId}
                            }
                        }
                    });
                    data.inst._set_settings({
                        contextmenu: {
                            items: {
                                movedown: {_disabled: data.inst._get_node().data('id') === lastNodeId}
                            }
                        }
                    });
                }
                if (!$(event.currentTarget).hasClass('jstree-loading')) {
                    data.inst.show_contextmenu($(data.args[0]), data.args[2].pageX, data.args[2].pageY);
                }
            },

            /**
             * Add action by default.
             * @param {Event} event
             * @param {Object} data
             * @private
             */
            _onAdd: function(event, data) {
                this.jsTree.jstree('create', data.inst._get_node());
            },

            /**
             * Select action by default.
             * @param {Object} selectedNode
             * @return {Object}
             * @private
             */
            _jstreeSelectNode: function(selectedNode) {
                if (this.jsTreeSettings.isDrawer) {
                    app.drawer.close(selectedNode);
                } else {
                    return selectedNode;
                }
            },

            /**
             * Add action.
             * @param {String} title
             * @param {String|Number} position
             * @param {Boolean} editable
             * @param {Boolean} addToRoot
             */
            addNode: function(title, position, editable, addToRoot) {
                var self = this,
                    selectedNode = (addToRoot === true) ? [] : this.jsTree.jstree('get_selected'),
                    pos = position || 'last',
                    isEdit = editable || false;

                this.jsTree.jstree(
                    'create',
                    selectedNode,
                    pos,
                    {data: !_.isUndefined(title) ? title : 'New item'},
                    function(obj) {
                        if (self.collection.length === 0) {
                            self._toggleVisibility(false);
                        }
                    },
                    isEdit
                );
            },

            /**
             * Select node in tree.
             * @param {String} id
             */
            selectNode: function(id) {
                var node = this.jsTree.find('[data-id=' + id + ']');
                this.jsTree.jstree('select_node', node);
                node.addClass('jstree-clicked');
            },

            /**
             * Insert node into tree.
             * @param {Object} data
             * @param {String} parent_id
             * @param {String} type
             */
            insertNode: function(data, parent_id, type) {
                var selectedNode = this.jsTree.find('[data-id=' + parent_id + ']');
                this.jsTree.jstree('create', selectedNode, 'last', {data: data.name, id: data.id}, function(obj) {
                    $(obj).data('id', data.id).data('type', type || 'folder');
                    $(obj).attr('data-id', data.id);
                    $(obj).find('ins:first').addClass('leaf');
                    obj.hide();
                }, true);
                this.jsTree.jstree('toggle_node', selectedNode);
            },

            /**
             * Save state of tree.
             */
            saveJSTreeState: function () {
                _.defer(function(jstree) {
                    jstree.jstree('save_state');
                }, this.jsTree);
            },

            /**
             * Load state of tree.
             */
            loadJSTreeState: function () {
                _.defer(function(jstree) {
                    jstree.jstree('load_state');
                }, this.jsTree);
            },

            /**
             * Open required node.
             * @param {String} id
             */
            openNode: function(id) {
                var selectedNode = this.jsTree.find('[data-id=' + id +']');
                if (selectedNode.hasClass('jstree-closed')) {
                    this.jsTree.jstree('open_node', selectedNode);
                }
            },

            /**
             * Show child nodes which were added by insertNode.
             * @param {String} id
             */
            showChildNodes: function(id) {
                var selectedNode = this.jsTree.find('[data-id=' + id +']');
                selectedNode.children("ul:eq(0)").children("li").show();
            },
            /**
             * Hide child nodes to prevent open/close folder.
             * @param {String} id
             */
            hideChildNodes: function(id) {
                var selectedNode = this.jsTree.find('[data-id=' + id +']');
                selectedNode.children("ul:eq(0)").children("li").hide();
            },

            /**
             * Removes children with provided type for the node.
             * @param {String} id
             * @param {String} type
             */
            removeChildrens: function (id, type) {
                var currentNode = this.jsTree.find('[data-id=' + id +']'),
                    childrens = currentNode.children("ul:eq(0)").children("li");
                type = type || 'folder';
                _.each(childrens, function(child) {
                    if ($(child).data('type') === type) {
                        this.jsTree.jstree('delete_node', child);
                    }
                }, this);

            },

            /**
             * Clear selected nodes.
             */
            clearSelection: function() {
                this.jsTree.jstree('deselect_all');
            },

            /**
             * Search action.
             * @param {String} searchString
             */
            searchNode: function(searchString) {
                this.jsTree.jstree('clear_search');
                this.jsTree.jstree('close_all');

                if (!_.isUndefined(searchString)) {
                    this.jsTree.jstree('search', searchString);
                }
            },

            /**
             * Move action.
             * @param {String} idRecord
             * @param {String} idTarget
             * @param {String} position
             * @param {Function} callback
             */
            moveNode: function(idRecord, idTarget, position, callback) {
                var self = this,
                    pos = position || 'last',
                    method = 'move' + pos.charAt(0).toUpperCase() + pos.substring(1).toLowerCase();

                if (idRecord === idTarget) {
                    app.alert.show('wrong_path_confirmation', {
                        level: 'error',
                        messages: app.lang.get('LBL_WRONG_MOVE_PATH', this.module)
                    });
                }

                if (_.isFunction(this.collection[method]) && (idRecord !== idTarget)) {
                    this.collection[method]({
                        record: idRecord,
                        target: idTarget,
                        success: function(data, response) {
                            if (!_.isUndefined(callback)) {
                                callback(data, response);
                            }
                        }
                    });
                }
            },

            /**
             * Refresh context menu.
             * @private
             */
            _refreshContextMenu: function() {
                // Set items to null due to avoid merge from $.extend
                $.jstree._focused()._set_settings({contextmenu: {items: {moveto: {submenu: null}}}});
                // Set items to updated list
                $.jstree._focused()._set_settings({contextmenu: {items: {moveto: {submenu: this._buildRootsSubmenu()}}}});
            }
        });
    });

})(SUGAR.App);
