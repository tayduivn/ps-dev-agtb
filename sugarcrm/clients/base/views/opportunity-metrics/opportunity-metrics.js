/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Views.Base.OpportunityMetricsView
 * @alias SUGAR.App.view.views.BaseOpportunityMetricsView
 * @extends View.View
 */
({
    plugins: ['Dashlet', 'Chart'],
    className: 'opportunity-metrics-wrapper',

    metricsCollection: null,

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.chart = nv.models.pieChart()
                .x(function(d) {
                    return d.key;
                })
                .y(function(d) {
                    return d.value;
                })
                .margin({top: 5, right: 20, bottom: 20, left: 20})
                .donut(true)
                .donutLabelsOutside(true)
                .donutRatio(0.447)
                .hole(this.total)
                .showTitle(false)
                .tooltips(true)
                .showLegend(false)
                .colorData('class')
                .tooltipContent(function(key, x, y, e, graph) {
                    return '<p><b>' + key + ' ' + parseInt(y, 10) + '</b></p>';
                })
                .strings({
                    noData: app.lang.getAppString('LBL_CHART_NO_DATA')
                });
    },

    /**
     * Generic method to render chart with check for visibility and data.
     * Called by _renderHtml and loadData.
     */
    renderChart: function() {
        if (!this.isChartReady()) {
            return;
        }

        // Set value of label inside donut chart
        this.chart.hole(this.total);

        d3.select(this.el).select('svg#' + this.cid)
            .datum(this.chartCollection)
            .transition().duration(500)
            .call(this.chart);

        this.chart_loaded = _.isFunction(this.chart.update);
        this.displayNoData(!this.chart_loaded);
    },

    /* Process data loaded from REST endpoint so that d3 chart can consume
     * and set general chart properties
     */
    evaluateResult: function(data) {
        var total = 0;

        _.each(data, function(value, key) {
            // parse currencies and attach the correct delimiters/symbols etc
            data[key].formattedAmount = app.currency.formatAmountLocale(value.amount_usdollar, null, 0);

            data[key].icon = key === 'won' ? 'caret-up' : (key === 'lost' ? 'caret-down' : 'minus');
            data[key].cssClass = key === 'won' ? 'won' : (key === 'lost' ? 'lost' : 'active');
            data[key].dealLabel = key;
            data[key].stageLabel = app.lang.getAppListStrings('opportunity_metrics_dom')[key];

            total += value.count;
        });

        this.total = total;
        this.metricsCollection = data;

        this.chartCollection = {
            data: _.map(this.metricsCollection, function(value, key) {
                return {
                    'key': value.stageLabel,
                    'value': value.count,
                    'classes': key
                };
            }),
            properties: {
                title: app.lang.getAppString('LBL_DASHLET_OPPORTUNITY_NAME'),
                value: 3,
                label: total
            }
        };
    },

    /**
     * @inheritDoc
     */
    loadData: function(options) {
        var self = this,
            url;
        if (this.meta.config) {
            return;
        }
        url = app.api.buildURL(this.model.module, 'opportunity_stats', {
            id: this.model.get('id')
        });
        app.api.call('read', url, null, {
            success: function(data) {
                self.evaluateResult(data);
                if (!self.disposed) {
                    // we have to rerender the entire dashlet, not just the chart,
                    // because the HBS file is dependant on metricsCollection
                    self.render();
                }
            },
            error: _.bind(function() {
                this.displayNoData(true);
            }, this),
            complete: options ? options.complete : null
        });
    }
})
