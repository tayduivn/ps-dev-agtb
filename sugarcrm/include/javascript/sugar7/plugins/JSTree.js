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
             * @param {View.Component} component The component this plugin is
             *   attached to.
             */
            onDetach: function(component) {
                if (!_.isEmpty(this.jsTree)) {
                    this.jsTree.off();
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
             * @param {Bool} hide Hide tree if true, show otherwise.
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
                /* @todo: handler for dnd move - wbi */
            },

            /**
             * Remove node handler.
             * @param {Event} event
             * @param {Object} data
             * @private
             */
            _removeHandler: function(event, data) {
                /* @todo: remove handler - wbi */
            },

            /**
             * Rename node handler.
             * @param {Event} event
             * @param {Object} data
             * @private
             */
            _renameHandler: function(event, data) {
                /* @todo: handler for rename node - wbi */
                /* @todo: update this.collection */
            },

            /**
             * Search node handler.
             * @param {Event} event
             * @param {Object} data
             * @private
             */
            _searchHandler: function(event, data) {
                /* @todo: handler for search node - wbi */
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
                                /*
                                 // @todo: waiting for edit implementation on backend
                                 */
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
                                // TODO: create method to get node from collection
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
                            self.moveTo(obj.data('id'), entry.id, function(idRecord, idTarget) {
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
                        name: data.rslt.obj.find('a:first').text().trim()
                    };
                    switch ($(data.args[0]).data('action')) {
                        case 'jstree-toggle':
                            if (this.jsTreeCallbacks.onToggle &&
                                !this.jsTreeCallbacks.onToggle.apply(this, [event, data])) {
                                return false;
                            }
                            this._jstreeToggle(event, data);
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

                this.collection.append({
                    target: parentId,
                    data: {name: node.title},
                    success: function(id) {
                        newNode.attr('data-id', id);
                        self._toggleVisibility(false);
                    },
                    error: function() {
                        //@todo: remove node - will be implemented
                    }
                });
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
             */
            addNode: function(title, position, editable) {
                var selectedNode = this.jsTree.jstree('get_selected'),
                    pos = position || 'last',
                    isEdit = !_.isUndefined(editable);

                this.jsTree.jstree(
                    'create',
                    selectedNode,
                    pos,
                    {data: !_.isUndefined(title) ? title : 'New item'},
                    function(obj) {},
                    isEdit
                );
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
             * Move up/down action.
             * @param {String} idRecord
             * @param {String} idTarget
             * @param {Function} callback
             */
            moveNode: function(idRecord, idTarget, callback) {
                this.collection.moveBefore({
                    record: idRecord,
                    target: idTarget,
                    success: function(data, response) {
                        callback(data, response);
                    }
                });
            },

            /**
             * Move to action.
             * @param {String} idRecord
             * @param {String} idTarget
             * @param {Function} callback
             */
            moveTo: function(idRecord, idTarget, callback) {
                var self = this;
                this.collection.moveLast({
                    record: idRecord,
                    target: idTarget,
                    success: function(data, response) {
                        callback(idRecord, idTarget);
                    }
                });
            }
        });
    });

})(SUGAR.App);
