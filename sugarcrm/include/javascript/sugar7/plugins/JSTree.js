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

    if (!$.fn.jstree) {
        return;
    }

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
             *
             * @param {Data.NestedSetCollection} collection Synced collection.
             */
            onNestedSetSyncComplete: function(collection) {
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
             * @param {String} settings.module_root Module parameter to build a collection (required).
             * @param {String} settings.category_root Root parameter to build a collection (required).
             * @param {Object} callbacks
             * @param {Object} callbacks.onToggle Callback on expand/collapse a tree branch.
             * @param {Object} callbacks.onLoad Callback on tree loaded.
             * @param {Object} callbacks.onLeaf Callback on leaf click.
             * @param {Object} callbacks.onShowContextmenu Callback on show a context menu.
             * @param {Object} callbacks.onAdd Callback on add a new node.
             * @param {Object} callbacks.onSelect Callback on select a node.
             * @private
             */
            _renderTree: function($container, settings, callbacks) {
                var options = {};

                this.jsTreeSettings = settings || {};
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
                        this.createTree(data.jsonTree, this.$treeContainer);
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
             * Create JSTree.
             * @param {Object} data
             * @param {Object} $container
             */
            createTree: function(data, $container) {
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
                        }

                        el.children = el.children.records;
                        el.data = el.name;
                        el.metadata = {id: el.id};
                        el.attr = {'data-id': el.id, 'data-level': el.level};
                    };

                treeData.ctx = this.context;

                _.each(treeData, fn);

                this.jsTree = $container.jstree({
                    core: {
                        html_titles: true
                    },
                    settings: this.jsTreeSettings,
                    plugins: ['json_data', 'dnd', 'ui', 'crrm', 'types', 'themes', 'contextmenu', 'search'],
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
                })
                .on('loaded.jstree', _.bind(function() {
                    this._loadedHandler($container);
                }, this))
                .on('select_node.jstree', _.bind(this._selectNodeHandler, this))
                .on('create.jstree', _.bind(this._createHandler, this))
                .on('move_node.jstree', _.bind(this._moveHandler, this))
                .on('remove.jstree', _.bind(this._removeHandler, this))
                .on('rename_node.jstree', _.bind(this._renameHandler, this))
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
                        this.moveNode(data.rslt.o.data('id'), data.rslt.r.data('id'), data.rslt.p, function() {});
                    }
                }
            },

            /**
             * Remove node handler.
             * @param {Event} event
             * @param {Object} data
             * @private
             */
            _removeHandler: function(event, data) {
                /* ToDo: remove handler - wbi */
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
                                 // ToDo: waiting for edit implementation on backend
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
                                // ToDo: create method to get node from collection
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
                        separator_before: false,
                        icon: 'jstree-icon',
                        separator_after: false,
                        label: entry.get('name'),
                        action: function(obj) {
                            self.moveNode(obj.data('id'), entry.id, 'last', function(idRecord, idTarget) {
                                self.jsTree.jstree(
                                    'move_node',
                                    self.jsTree.jstree('get_instance')
                                        .get_container_ul()
                                        .find('li[data-id=' + idRecord + ']'),
                                    self.jsTree.jstree('get_instance')
                                        .get_container_ul()
                                        .find('li[data-id=' + idTarget + ']')
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
                            if (this.jsTreeCallbacks.onToggle &&
                                !this.jsTreeCallbacks.onToggle.apply(this, [event, data])) {
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
                        success: function(id) {
                            newNode.attr('data-id', id);
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
             * @param {Boolean} position
             */
            addNode: function(title, position, editable) {
                var self = this,
                    selectedNode = this.jsTree.jstree('get_selected'),
                    pos = position || 'last',
                    isEdit = editable || !_.isUndefined(editable);

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
             * Insert node into tree.
             * @param {Object} data
             * @param {String} parent_id
             * @param {String} type
             */
            insertNode: function(data, parent_id, type) {
                var selectedNode = this.jsTree.find('[data-id=' + parent_id + ']');
                this.jsTree.jstree('create', selectedNode, 'last', {data: data.name, id: data.id}, function(obj) {
                    debugger;
                    $(obj).data('id', data.id).data('type', type || 'folder');
                    $(obj).find('ins:first').addClass('leaf');
                }, true);
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
                var pos = position || 'last',
                    method = 'move' + pos.charAt(0).toUpperCase() + pos.substring(1).toLowerCase();
                if (_.isFunction(this.collection[method])) {
                    this.collection[method]({
                        record: idRecord,
                        target: idTarget,
                        success: function(data, response) {
                            callback(data, response);
                        }
                    });
                }
            }
        });
    });

})(SUGAR.App);
