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

    /**
     * List of tree nodes
     */
    nodes: null,

    /**
     * ID of tree item which is in active state (currently selected)
     */ 
    active: null,

    /**
     * Initialize dashlet properties
     */
    initDashlet: function() {
        var listTpl = app.template.getView('kbs-dashlet-topics.node-list', this.module),
            topicId = this.model.get('topic_id');

        Handlebars.registerPartial('kbs-dashlet-topics.node-list', listTpl);
        this.active = !_.isUndefined(topicId) ? topicId : null;
    },

    /**
     * Re-render the dashlet when the model data changed.
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
        var link = this.settings.get('link_name');
        var module = this.settings.get('module');

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
                self.render();
            }
        });
    },

    /**
     * {@inheritDocs}
     */
    _render: function() {
        this._super('_render');
        if (_.isObject(this.nodes) && this.active != null) {
            this.$('[data-node-id="' + this.active + '"]').addClass('active');
        }
    }
})
