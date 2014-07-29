/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
({

    plugins: ['Dashlet'],

    events: {
        'click [data-node-icon="folder"]': 'toggleNode'
    },

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
     * Last state key.
     *
     * @property {String}
     */
    _lastStateKey: null,

    /**
     * Initialize dashlet properties.
     */
    initDashlet: function() {
        var listTpl = app.template.getView('kbs-dashlet-topics.node-list', this.module);
        Handlebars.registerPartial('kbs-dashlet-topics.node-list', listTpl);
    },

    /**
     * Build the state key for nodes.
     *
     * @return {String} hash key.
     */
    getLastStateKey: function() {
        if (this._lastStateKey) {
            return this._lastStateKey;
        }
        this._lastStateKey = app.user.lastState.key('nodes-expanded', this);
        return this._lastStateKey;
    },

    /**
     * {@inheritDocs}
     */
    bindDataChange: function(){
        if (this.model) {
            this.model.on('change', this.loadData, this);
        }
    },

    /**
     * {@inheritDocs}
     */
    loadData: function(options) {
        var link = this.settings.get('link_name'),
            module = this.settings.get('module_name'),
            topicId = app.controller.context.get('model').get('topic_id');

        this.active = !_.isUndefined(topicId) ? topicId : null;    

        if (this.disposed || !link || !module) {
            return;
        }

        var url = app.api.buildURL(module, 'tree/' + link, null, {
                'order_by' : 'name:asc'
            }), 
            self = this;

        app.api.call('read', url, null, {
            success:  function(data) {
                if (this.disposed) {
                    return;
                }
                self.nodes = data;

                if (self.active) {
                    var url = app.api.buildURL('KBSContents', null, null, {
                        'max_num' : -1,
                        'fields' : 'name,topic_id',
                        'order_by' : 'name:asc',
                        'filter': [
                            {
                                'topic_id': self.active,
                                'active_rev': 1
                            }
                        ]
                    });
                    app.api.call('read', url, null, {
                        success:  function(data) {
                            if (self.disposed) {
                                return;
                            }

                            self._traverseNodes(self.nodes, function(node) {
                                if (node.id == self.active) {
                                    if (_.has(node.subnodes, 'records')) {
                                        node.subnodes.records = _.union(node.subnodes.records, data.records);
                                    } else {
                                        node = _.extend(node, {
                                            'subnodes' : data
                                        });    
                                    }
                                    return false;
                                }
                                return true;
                            });

                            self.render();
                        }
                    });
                } else {
                    self.render();
                }
            }
        });
    },

    /**
     * Change open/close node state and save to last state.
     *
     * @param {Event} event 
     */
    toggleNode: function(event) {
        event.preventDefault();
        var $sender = $(event.currentTarget),
            $node = $sender.parent('[data-node-id]'),
            $subnodes = $node.find('[data-type="node-list"]:first'),
            nodeId = $node.data('node-id'),
            nodesExpanded = _.without(app.user.lastState.get(this.getLastStateKey()) || [], nodeId);

        $subnodes.toggle();
        $sender.blur();
        $node.find('[data-node-icon="folder"]:first').toggleClass('icon-folder-open-alt', $subnodes.is(':visible'));

        if ($subnodes.is(':visible')) {
            nodesExpanded.push(nodeId);
        }

        app.user.lastState.set(this.getLastStateKey(), nodesExpanded);
    },

    /**
     * Traversing a nodes of tree and calling user-specified callbacks.
     *
     * @param {Object} nodes List of tree nodes.
     * @param {Function} callback User specified function that will be called for each node.
     */
    _traverseNodes: function(nodes, callback) {
        if (!_.isFunction(callback)) {
            return false;
        }

        _.each(nodes.records, function(record) {
            if (callback.apply(this, [record])) {
                this._traverseNodes(record.subnodes, callback);    
            }
            return false;
        }, this);
    },

    /**
     * Update node attributes to make it UI in active state.
     *
     * @param {Mixed} node Node ID or jQuery element collection that should be activated.
     * @param {Boolean} activateParents whether to activate parent tree nodes when one of the corresponding child nodes is active.
     */
    _activateItem: function(node, activateParents) {
        var self = this,
            $node = _.isObject(node) ? node : this.$('[data-node-id="' + node + '"]');

        activateParents = activateParents || false;

        $node.find('[data-node-icon="folder"]:first')
            .addClass('icon-folder-open-alt')
            .toggleClass('active', (_.isObject(node) || $node.data('node-id') == this.active));

        if (activateParents == true) {
            _.each($node.parents('[data-type="node-list"]'), function(list) {
                self._activateItem($(list).show().parent());
            });
        } else {
            $node.find('[data-type="node-list"]:first').show();
        }
    },

    /**
     * {@inheritDocs}
     */
    _render: function() {
        this._super('_render');
        if (!_.isObject(this.nodes)) {
            return;
        }
        this.$('[data-type="node-list"] [data-type="node-list"]').hide();
        _.each(app.user.lastState.get(this.getLastStateKey()), _.bind(this._activateItem, this));
        if (this.active != null) {
            this._activateItem(this.active, true);
        }
    }
})
