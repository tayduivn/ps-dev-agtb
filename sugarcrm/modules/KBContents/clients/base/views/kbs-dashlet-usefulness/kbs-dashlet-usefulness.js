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
                    print: '',
                    subtitle: '',
                    thousands: '',
                    title: '',
                    type: 'pie chart'
                }
            ],
            values: [
                {
                    label: [app.lang.get('LBL_USEFUL', 'KBContents')],
                    values: [useful],
                    classes: 'nv-fill-green'
                },
                {
                    label: [app.lang.get('LBL_NOT_USEFUL', 'KBContents')],
                    values: [notuseful],
                    classes: 'nv-fill-red'
                }
            ]
            },
            chartParams = {
                donut: true,
                donutRatio: 0.45,
                hole: parseInt(useful*100/(notuseful + useful)) +  '%',
                donutLabelsOutside: true,
                colorData: 'data',
                chart_type: 'pie chart',
                show_legend: false
        };

        _.defer(_.bind(function() {
            this.chartData.set({rawChartData: chartData, rawChartParams: chartParams});
        }, this));
        if (options && _.isFunction(options.complete)) {
            options.complete();
        }
    },

    /**
     * {@inheritDocs}
     *
     * Dispose listeners for 'change:useful' and 'change:notuseful' events.
     */
    dispose: function() {
        this.stopListening(app.controller.context.get('model'), 'change:useful', this.refresh);
        this.stopListening(app.controller.context.get('model'), 'change:notuseful', this.refresh);
        this._super('dispose');
    }
})
