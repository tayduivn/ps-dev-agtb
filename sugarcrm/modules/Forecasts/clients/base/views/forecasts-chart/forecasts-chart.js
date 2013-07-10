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
     * Hold the initOptions if we have to call the Forecast/init end point cause we are not on Forecasts
     */
    initOptions: null,

    /**
     * The context of the ForecastManagerWorksheet Module if one exists
     */
    forecastManagerWorksheetContext: undefined,

    /**
     * The context of the ForecastWorksheet Module if one exists
     */
    forecastWorksheetContext: undefined,

    /**
     * Data that is currently in 3d
     */
    d3Data: {},

    /**
     * {@inheritdoc}
     */
    initialize: function(options) {
        var fieldOptions,
            cfg = app.metadata.getModule('Forecasts', 'config');
        this.values.clear({silent: true});
        // after we init, find and bind to the Worksheets Contexts
        this.on('init', this.findWorksheetContexts, this);
        if (options.context.parent.get('module') != 'Forecasts') {
            this.initOptions = options;
            app.api.call('GET', app.api.buildURL('Forecasts/init'), null, {
                success: _.bind(function(o) {
                    app.view.View.prototype.initialize.call(this, this.initOptions);
                    this.values.set({
                        user_id: this.context.parent.get('selectedUser').id,
                        display_manager: this.context.parent.get('selectedUser').is_manager,
                        timeperiod_id: o.defaultSelections.timeperiod_id.id,
                        timeperiod_label: o.defaultSelections.timeperiod_id.label,
                        dataset: o.defaultSelections.dataset,
                        group_by: o.defaultSelections.group_by,
                        ranges: o.defaultSelections.ranges
                    });
                }, this),
                complete: options ? options.complete : null
            });
        } else {
            app.view.View.prototype.initialize.call(this, options);
            var ctx = this.context.parent,
                user = ctx.get('selectedUser');
            this.values.set({
                user_id: user.id,
                display_manager: user.is_manager,
                ranges: ctx.get('selectedRanges'),
                timeperiod_id: ctx.get('selectedTimePeriod'),
                dataset: 'likely',
                group_by: 'forecast'
            });
        }
        fieldOptions = app.lang.getAppListStrings(this.dashletConfig.dataset.options);
        this.dashletConfig.dataset.options = {};

        if (cfg.show_worksheet_worst) {
            this.dashletConfig.dataset.options['worst'] = fieldOptions['worst'];
        }

        if (cfg.show_worksheet_likely) {
            this.dashletConfig.dataset.options['likely'] = fieldOptions['likely'];
        }

        if (cfg.show_worksheet_best) {
            this.dashletConfig.dataset.options['best'] = fieldOptions['best'];
        }

        // after we render the dashlet, fetch and render the chart
        this.once('render', function() {
            this.renderChart();
        }, this);
    },

    /**
     * Loop though the parent context children context to find the worksheet, if they exist
     */
    findWorksheetContexts: function() {
        // loop though the children context looking for the ForecastWorksheet and ForecastManagerWorksheet Modules
        _.filter(this.context.parent.children, function(item) {
            if (item.get('module') == 'ForecastWorksheets') {
                this.forecastWorksheetContext = item;
                return true;
            } else if (item.get('module') == 'ForecastManagerWorksheets') {
                this.forecastManagerWorksheetContext = item;
                return true;
            }
            return false;
        }, this);

        if (this.forecastWorksheetContext) {
            // listen for collection change events
            var collection = this.forecastWorksheetContext.get('collection');
            collection.on('change', this.repWorksheetChanged, this);
        }

        if (this.forecastManagerWorksheetContext) {
            // listen for collection change events
            var collection = this.forecastManagerWorksheetContext.get('collection');
            collection.on('change', this.mgrWorksheetChanged, this);
        }
    },

    repWorksheetChanged: function(model) {

    },

    mgrWorksheetChanged: function(model) {

    },

    /**
     * We never want to have the loadData method do anything
     *
     * {@override}
     */
    loadData: function(options) {
        return;
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
        if (this.meta.config) {
            return;
        }

        this.on('render', function() {
            this.toggleRepOptionsVisibility();
        }, this);

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
     * {@inheritdoc}
     * Clean up!
     */
    unbindData: function() {
        var ctx = this.context.parent;
        if (ctx) {
            ctx.off(null, null, this);
        }
        if (this.forecastManagerWorksheetContext) {
            this.forecastManagerWorksheetContext.get('collection').off(null, null, this);
        }
        if (this.forecastWorksheetContext) {
            this.forecastWorksheetContext.get('collection').off(null, null, this);
        }
        app.view.View.prototype.unbindData.call(this);
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

        if (this.values.get('display_manager') === true) {
            this.values.set({ranges: 'include'}, {silent: true});
        }

        var params = this.values.toJSON() || {};
        params.contentEl = 'chart';
        params.minColumnWidth = 120;
        params.type = app.metadata.getModule('Forecasts', 'config').forecast_by;

        var data = $.extend({
                r: new Date().getTime()
            }, params),
            url = app.api.buildURL(this.buildChartUrl(params), '', '', data);

        app.api.call('read', url, data, {
            success: _.bind(function(data) {
                this.layout.$el.find('h4').html(
                    this.layout.meta.label + ' ' + data.properties[0].title
                );
                this.translateDataToD3(data);
                console.log(this.d3Data);
                this.generateD3Chart()
            }, this)
        });
    },

    generateD3Chart: function() {

        var params = this.values.toJSON(),
            chartId = params.chartId || 'db620e51-8350-c596-06d1-4f866bfcfd5b',
            paretoChart = nv.models.paretoChart()
            .margin({top: 0, right: 10, bottom: 20, left: 30})
            .showTitle(false)
            .tooltips(true)
            .tooltipLine(function(key, x, y, e, graph) {
                // Format the value using currency class and user settings
                var val = App.currency.formatAmountLocale(e.point.y)
                return '<p>' + key + ': <b>' + val + '</b></p>'
            })
            .tooltipBar(function(key, x, y, e, graph) {
                // Format the value using currency class and user settings
                var val = App.currency.formatAmountLocale(e.value)
                return '<p>' + SUGAR.App.lang.get('LBL_SALES_STAGE', 'Forecasts') + ': <b>' + key + '</b></p>' +
                    '<p>' + SUGAR.App.lang.get('LBL_AMOUNT', 'Forecasts') + ': <b>' + val + '</b></p>' +
                    '<p>' + SUGAR.App.lang.get('LBL_PERCENT', 'Forecasts') + ': <b>' + x + '%</b></p>'
            })
            .showControls(false)
            .colorData('default')
            .colorFill('default')
            .stacked(!params.display_manager)
            .id(chartId);

        d3.select('#' + chartId + ' svg').remove();

        // After the .call(paretoChart) line, we are selecting the text elements for the Y-Axis
        // only so we can custom format the Y-Axis values
        d3.select('#' + chartId)
            .append('svg')
            .datum(this.d3Data)
            .transition().duration(500)
            .call(paretoChart)
            .selectAll('.nv-y.nv-axis .tick')
            .select('text')
            .text(function(d) {
                return App.user.get('preferences').currency_symbol + d3.format(',.2s')(d);
            });

        nv.utils.windowResize(paretoChart.update);
    },

    translateDataToD3: function(json) {
        this.d3Data = {
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
                            }),
                            'start': json.values[i].start_timestamp,
                            'end': json.values[i].end_timestamp
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
