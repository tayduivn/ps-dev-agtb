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
/**
 * Dashlet that displays a chart
 */
({
    plugins: ['Dashlet'],

    values: new Backbone.Model(),
    chart: null,
    className: 'forecasts-chart-wrapper',

    /**
     * {@inheritdoc}
     */
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.values.clear({silent: true});
        app.api.call('GET', app.api.buildURL('Forecasts/init'), null, {
            success: _.bind(function(o) {
                this.values.set({
                    timeperiod_id: o.defaultSelections.timeperiod_id.id,
                    timeperiod_label: o.defaultSelections.timeperiod_id.label,
                    dataset: o.defaultSelections.dataset,
                    group_by: o.defaultSelections.group_by,
                    ranges: o.defaultSelections.ranges
                });
            }, this),
            complete: options ? options.complete : null
        });
    },

    /**
     * {@inheritdoc}
     */
    loadData: function(options) {
        this.renderChart();
    },

    /**
     * {@inheritdoc}
     *
     * @protected
     */
    _renderHtml: function(ctx, options) {
        app.view.View.prototype._renderHtml.call(this, ctx, options);

        this.values.set({
            user_id: app.user.get('id'),
            display_manager: app.user.get('isManager')
        });
    },

    /**
     * {@inheritdoc}
     */
    _render: function() {
        app.view.View.prototype._render.call(this);
        this.toggleRepOptionsVisibility();
    },

    /**
     * Called after _render
     */
    toggleRepOptionsVisibility: function() {
        if (this.values.get('display_manager') === true) {
            this.$el.find('div.groupByOptions').hide();
        } else {
            this.$el.find('div.groupByOptions').show();
        }
    },

    /**
     * {@inheritdoc}
     *
     * Clean up any left over bound data to our context
     */
    _dispose: function() {
        if (this.context) this.context.off(null, null, this);
        if (this.context.parent) this.context.parent.off(null, null, this);
        if (this.values) this.values.off(null, null, this);
        app.view.View.prototype._dispose.call(this);
    },

    /**
     * {@inheritdoc}
     */
    bindDataChange: function() {
        this.context.parent.on('forecasts:worksheet:committed', function() {
            this.renderChart();
        }, this);
        this.context.parent.on('forecasts:worksheet:saved', function(totalSaved, worksheet, isDraft) {
            // we only want this to run if the totalSaved was greater than zero and we are saving the draft version
            if (totalSaved > 0 && isDraft == true) {
                this.renderChart();
            }
        }, this);
        this.context.parent.on('change:selectedUser', function(context, user) {
            if (!_.isEmpty(this.chart)) {
                this.values.set({
                    user_id: user.id,
                    display_manager: (user.showOpps === false && user.isManager === true)
                });
                this.toggleRepOptionsVisibility();
            }
        }, this);
        this.context.parent.on('change:selectedTimePeriod', function(context, timePeriod) {
            if (!_.isEmpty(this.chart)) {
                this.values.set({timeperiod_id: timePeriod});
            }
        }, this);
        this.context.parent.on('change:selectedRanges', function(context, value) {
            if (!_.isEmpty(this.chart)) {
                this.values.set({ranges: value});
            }
        }, this);
        this.values.on('change', function(value) {
            this.renderChart();
        }, this);
    },

    /**
     * Render the chart for the first time
     *
     * @private
     */
    renderChart: function() {

        if (this.disposed) {
            return;
        }

        var chart,
            chartId = 'db620e51-8350-c596-06d1-4f866bfcfd5b',
            css = {
                'gridLineColor': '#cccccc',
                'font-family': 'Arial',
                'color': '#000000'
            },
            chartConfig = {
                'orientation': 'vertical',
                'barType': this.values.get('display_manager') ? 'grouped' : 'stacked',
                'tip': 'name',
                'chartType': 'd3-barChart',
                'imageExportType': 'png',
                'showNodeLabels': false,
                'showAggregates': false,
                'saveImageTo': '',
                'dataPointSize': '5'
            },
            oldChart = $('#' + chartId);

        if (!_.isEmpty(oldChart)) {
            d3.select('#' + chartId + ' svg').remove();
        }

        SUGAR.charts = $.extend(SUGAR.charts,
            {
                get: _.bind(function(url, params, success) {
                    var data = {
                        r: new Date().getTime()
                    };
                    data = $.extend(data, params);

                    url = app.api.buildURL(this.buildChartUrl(params), '', '', data);

                    app.api.call('read', url, data, {
                        success: _.bind(function(data) {
                            this.layout.$el.find('h4').html(
                                this.layout.meta.label + ' ' + data.properties[0].title
                            );
                            success(data);
                        }, this)
                    });
                }, this),
                translateDataToD3: function(json, params) {
                    return {
                        'properties': {
                            'title': json.properties[0].title, 'quota': parseInt(json.values[0].goalmarkervalue[0], 10),
                            // bar group data (x-axis)
                            'groupData': (!json.values.filter(function(d) {
                                return d.values.length;
                            }).length) ? [] :
                                json.values.map(function(d, i) {
                                    return {
                                        'group': i,
                                        'l': json.values[i].label,
                                        't': json.values[i].values.reduce(function(p, c, i, a) {
                                            return parseInt(p, 10) + parseInt(c, 10);
                                        })
                                    };
                                })
                        },
                        // series data
                        'data': (!json.values.filter(function(d) {
                            return d.values.length;
                        }).length) ? [] :
                            json.label.map(function(d, i) {
                                return {
                                    'key': d, 'type': 'bar', 'series': i, 'values': json.values.map(function(e, j) {
                                        return { 'series': i, 'x': j + 1, 'y': parseInt(e.values[i], 10), y0: 0 };
                                    }), 'valuesOrig': json.values.map(function(e, j) {
                                        return { 'series': i, 'x': j + 1, 'y': parseInt(e.values[i], 10), y0: 0 };
                                    })
                                };
                            }).concat(
                                    json.properties[0].goal_marker_label.filter(function(d, i) {
                                        return d !== 'Quota';
                                    }).map(function(d, i) {
                                            return {
                                                'key': d,
                                                'type': 'line',
                                                'series': i,
                                                'values': json.values.map(function(e, j) {
                                                    return {
                                                        'series': i,
                                                        'x': j + 1,
                                                        'y': parseInt(e.goalmarkervalue[i + 1], 10)
                                                    };
                                                }), 'valuesOrig': json.values.map(function(e, j) {
                                                    return {
                                                        'series': i,
                                                        'x': j + 1,
                                                        'y': parseInt(e.goalmarkervalue[i + 1], 10)
                                                    };
                                                })
                                            };
                                        })
                                )
                    };
                }
            }
        );

        if (this.values.get('display_manager') === true) {
            this.values.set({ranges: 'include'}, {silent: true});
        }

        var params = this.values.toJSON() || {};
        params.contentEl = 'chart';
        params.minColumnWidth = 120;
        params.chartId = chartId;
        params.type = app.metadata.getModule('Forecasts', 'config').forecast_by;

        chart = new loadSugarChart(
            chartId,
            this.buildChartUrl(params),
            css,
            chartConfig,
            params,
            _.bind(function(chart) {
                this.chart = chart;
            }, this));
    },

    /**
     * Accepts params object and builds the proper endpoint url for charts
     *
     * @param {Object} params contains a lot of chart options and settings.
     * @return {String} has the proper structure for the chart url.
     */
    buildChartUrl: function(params) {
        var baseUrl = params.display_manager ? 'ForecastManagerWorksheets' : 'ForecastWorksheets';
        return baseUrl + '/chart/' + params.timeperiod_id + '/' + params.user_id;
    }

});
