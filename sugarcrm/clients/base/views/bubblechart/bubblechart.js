/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    plugins: ['Dashlet', 'GridBuilder'],

    events: {
        'click .toggle-control': 'switchChart'
    },

    filterAssigned: null,
    dateRange: [],
    dataset: {},
    params: {},
    chart: {},
    tooltiptemplate: {},

    initialize: function (options) {
        app.view.View.prototype.initialize.call(this, options);

        var self = this,
            fields = [
                'id',
                'name',
                'account_name',
                'likely_case',
                'base_rate',
                'currency_id',
                'assigned_user_name',
                'date_closed',
                'probability',
                'account_id',
                'sales_stage',
                'commit_stage'
            ];

        this.params = {
            'fields': fields.join(','),
            'max_num': 10,
            'order_by': 'likely_case:desc'
        };

        this.tooltiptemplate = app.template.getView(this.name + '.tooltiptemplate');

        this.filterAssigned = this.model.get('filter_assigned');

        this.setDateRange();

        this.chart = nv.models.bubbleChart()
            .x(function (d) {
                return d3.time.format('%Y-%m-%d').parse(d.x);
            })
            .y(function (d) {
                return d.y;
            })
            .tooltipContent(function (key, x, y, e, graph) {
                e.point.close_date = d3.time.format('%x')(d3.time.format('%Y-%m-%d').parse(e.point.x));
                e.point.amount = e.point.currency_symbol + d3.format(',.2d')(e.point.base_amount);
                return self.tooltiptemplate(e.point);
            })
            .showTitle(false)
            .tooltips(true)
            .showLegend(true)
            .bubbleClick(function (e) {
                app.router.navigate(app.router.buildRoute('Products', e.point.id), {trigger: true});
            })
            .colorData('class', {step:2})
            .groupBy(function (d) {
                return (self.filterAssigned === 'my') ? d.sales_stage_short : d.assigned_user_name;
            })
            .filterBy(function (d) {
                return d.probability;
            });

        this.on('data-changed', function () {
            this.updateChart();
        }, this);
        this.model.on('change:filter_duration', this.changeFilter, this);

        if (this.model.parentModel) {
            this.model.parentModel.on('change', this.loadData, this);
        }
    },

    initDashlet: function (view) {
        this.viewName = view;

        if (view === 'config') {
            // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
            app.view.invokeParent(this, {type: 'view', name: 'record', method: '_buildGridsFromPanelsMetadata', args:[this.meta.panels]});
        }
    },

    /**
     * Load data into chart model
     * and and set reference to chart
     */
    updateChart: function () {
        if (this.viewName === 'config') {
            return;
        }
        var self = this;
        //clear existing chart
        d3.select('svg#' + this.cid).select('.nvd3').remove();

        d3.select('svg#' + this.cid)
            .datum(self.dataset)
            .transition().duration(500)
            .call(self.chart);

        nv.utils.windowResize(self.chart.render);
    },

    /**
     * Filter out records that don'w meet date criteria
     * and convert into format convienient for d3
     */
    evaluateResult: function (data) {
        // TODO need i18n on this
        var salesStageMap = {
                'Negotiation/Review': 'Negotiat./Review',
                'Perception Analysis': 'Percept. Analysis',
                'Proposal/Price Quote': 'Proposal/Quote',
                'Id. Decision Makers': 'Id. Deciders'
            };
        this.dataset = {
            data: data.records.map(function (d) {
                return {
                    id: d.id,
                    x: d.date_closed,
                    y: Math.round(parseInt(d.likely_case, 10) / parseFloat(d.base_rate)),
                    shape: 'circle',
                    account_name: d.account_name,
                    assigned_user_name: d.assigned_user_name,
                    sales_stage: d.sales_stage,
                    sales_stage_short: salesStageMap[d.sales_stage] || d.sales_stage,
                    probability: parseInt(d.probability, 10),
                    base_amount: parseInt(d.likely_case, 10),
                    currency_symbol: app.currency.getCurrencySymbol(d.currency_id)
                };
            }),
            properties: {
                title: app.lang.getAppString('LBL_TOP10_OPPORTUNITIES_CHART'),
                value: data.records.length
            }
        };
    },

    /**
     * Request data from REST endpoint, evaluate result and trigger data change event
     */
    loadData: function (options) {
        var self = this,
            _filter = [
                {
                    'date_closed': {
                        '$gt': self.dateRange.begin
                    }
                },
                {
                    'date_closed': {
                        '$lte': self.dateRange.end
                    }
                }
            ];

        if (this.filterAssigned === 'my') {
            _filter.push({'$owner': ''});
        }

        var _local = _.extend({'filter': _filter}, this.params);

        var url = app.api.buildURL('Products', null, null, _local, this.params);

        app.api.call('read', url, null, {
            success: function (data) {
                self.evaluateResult(data);
                self.trigger('data-changed');
            },
            complete: options ? options.complete : null
        });
    },

    /**
     * Calculate date range based on date range dropdown control
     */
    setDateRange: function () {
        var now = new Date(),
            duration = parseInt(this.model.get('filter_duration'), 10),
            startMonth = Math.floor(now.getMonth() / 3) * 3,
            startDate = new Date(now.getFullYear(), (duration === 12 ? 0 : startMonth + duration), 1),
            endDate = new Date(now.getFullYear(), (duration === 12 ? 12 : startDate.getMonth() + 3), 0);
        this.dateRange = {
            'begin': app.date.format(startDate, 'Y-m-d'),
            'end': app.date.format(endDate, 'Y-m-d')
        };
    },

    /**
     * Trigger data load event based when date range dropdown changes
     */
    changeFilter: function () {
        this.setDateRange();
        this.loadData();
    },

    /**
     * Trigger data load event only when dataset toggle changes.
     */
    switchChart: function (e) {
        if (this.filterAssigned === e.currentTarget.value) {
            return;
        }
        this.filterAssigned = e.currentTarget.value;
        this.loadData();
    },

    _dispose: function () {
        if (this.model.parentModel) {
            this.model.parentModel.off('change', null, this);
        }
        this.model.off('change', null, this);
        this.on('data-changed', null, this);
        if (!_.isEmpty(this.chart)) {
            nv.utils.windowUnResize(this.chart.render);
        }
        app.view.View.prototype._dispose.call(this);
    }
})
