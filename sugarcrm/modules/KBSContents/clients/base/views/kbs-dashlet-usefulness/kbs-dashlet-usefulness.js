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
     * Holds report data from the server's endpoint once we fetch it
     */
    chartData: undefined,

    /**
     * We'll use this property to bind loadData function for event
     */
    refresh: null,

    /**
     * {@inheritDocs}
     */
    initialize: function(options) {
        this.chartData = new Backbone.Model();
        this._super('initialize', [options]);
        this.refresh = _.bind(this.loadData, this);
        this.listenTo(app.controller.context.get('model'), 'change:useful', this.refresh);
        this.listenTo(app.controller.context.get('model'), 'change:notuseful', this.refresh);
    },

    /**
     * {@inheritDocs}
     */
    loadData: function(options) {
        options = options || {};
        var dt = this.layout.getComponent('dashlet-toolbar');
        if (dt) {
            // manually set the icon class to spiny
            this.$('[data-action=loading]')
                .removeClass(dt.cssIconDefault)
                .addClass(dt.cssIconRefresh);
        }

        var useful = app.controller.context.get('model').get('useful') || '0';
        var notuseful = app.controller.context.get('model').get('notuseful') || '0';

        useful = parseInt(useful, 10);
        notuseful = parseInt(notuseful, 10);

        // correcting values for pie chart,
        // because pie chart not support all zero values.
        if (0 === useful && 0 === notuseful) {
            useful = 1;
            notuseful = 1;
        }

        var chartData = {
            properties: [
                {
                    labels: 'value',
                    legend: 'on',
                    print: '',
                    subtitle: '',
                    thousands: '',
                    title: '',
                    type: 'pie chart'
                }
            ],
            values: [
                {
                    label: [app.lang.get('LBL_USEFUL', 'KBSContents')],
                    values: [useful]
                },
                {
                    label: [app.lang.get('LBL_NOT_USEFUL', 'KBSContents')],
                    values: [notuseful]
                }
            ]
        };

        _.defer(_.bind(function() {
            this.chartData.set({rawChartData: chartData});
        }, this));
        if (options && _.isFunction(options.complete)) {
            options.complete();
        }
    },

    dispose: function() {
        this.stopListening(app.controller.context.get('model'), 'change:useful', this.refresh);
        this.stopListening(app.controller.context.get('model'), 'change:notuseful', this.refresh);
        this._super('dispose');
    }
})
