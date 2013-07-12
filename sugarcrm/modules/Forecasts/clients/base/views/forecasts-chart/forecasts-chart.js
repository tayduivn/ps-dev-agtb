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

    serverData: {},

    /**
     * Data that is currently in 3d
     */
    d3Data: {},

    /**
     * {@inheritdoc}
     */
    initialize: function(options) {
        this.values.clear({silent: true});
        if (options.context.parent.get('module') != 'Forecasts') {
            this.initOptions = options;
            app.api.call('GET', app.api.buildURL('Forecasts/init'), null, {
                success: _.bind(function(o) {
                    app.view.View.prototype.initialize.call(this, this.initOptions);
                    this.values.set({
                        user_id: app.user.get('id'),
                        display_manager: o.initData.userData.isManager,
                        timeperiod_id: o.defaultSelections.timeperiod_id.id,
                        timeperiod_label: o.defaultSelections.timeperiod_id.label,
                        dataset: o.defaultSelections.dataset,
                        group_by: o.defaultSelections.group_by,
                        ranges: o.defaultSelections.ranges
                    });
                    this.renderChart();
                }, this),
                complete: options ? options.complete : null
            });
        } else {
            // after we init, find and bind to the Worksheets Contexts
            this.on('init', this.findWorksheetContexts, this);
            app.view.View.prototype.initialize.call(this, options);
            if(!this.meta.config) {
                var ctx = this.context.parent,
                    user = ctx.get('selectedUser') || app.user.toJSON();
                this.values.set({
                    user_id: user.id,
                    display_manager: user.is_manager,
                    ranges: ctx.get('selectedRanges') || ['include'],
                    timeperiod_id: ctx.get('selectedTimePeriod'),
                    dataset: 'likely',
                    group_by: 'forecast'
                });

                this.once('render', function() {
                    this.renderChart();
                }, this);
            }
        }
    },

    /**
     * Specific code to run after a dashlet Init Code has ran
     *
     */
    initDashlet: function() {
        var fieldOptions,
            cfg = app.metadata.getModule('Forecasts', 'config');
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
            if (collection) {
                collection.on('change', this.repWorksheetChanged, this);
            }
        }

        if (this.forecastManagerWorksheetContext) {
            // listen for collection change events
            var collection = this.forecastManagerWorksheetContext.get('collection');
            if (collection) {
                collection.on('change', this.mgrWorksheetChanged, this);
            }
        }
    },

    /**
     * Handler for when the Rep Worksheet Changes
     * @param {Object} model
     */
    repWorksheetChanged: function(model) {
        // get what we are currently filtered by
        // find the item in the serverData
        var changed = model.changed,
            changedField = _.keys(changed);

        if (_.contains(changedField, 'likely_case')) {
            changed.likely = app.currency.convertWithRate(changed.likely_case, model.get('base_rate'));
            delete changed.likely_case;
        }
        if (_.contains(changedField, 'best_case')) {
            changed.best = app.currency.convertWithRate(changed.best_case, model.get('base_rate'));
            delete changed.best_case;
        }
        if (_.contains(changedField, 'worst_case')) {
            changed.worst = app.currency.convertWithRate(changed.worst_case, model.get('base_rate'));
            delete changed.worst_case;
        }

        if (_.contains(changedField, 'commit_stage')) {
            changed.forecast = changed.commit_stage
            delete changed.commit_stage;
        }

        _.find(this.serverData.data, function(record, i, list) {
            if (model.get('id') == record.id) {
                list[i] = _.extend({}, record, changed);
                return true;
            }
            return false;
        });

        // if Probability was adjusted, redo the keys for the Probability labels
        if (_.contains(changedField, 'probability')) {
            this.adjustProbabilityLabels();
        }
        this.convertDataToChartData();
        this.generateD3Chart();
    },

    /**
     * When the Probability Changes on the Rep Worksheet, The labels in the chart data need to be updated
     * to Account for the potentially new label.
     */
    adjustProbabilityLabels: function() {
        var probabilities = [];
        _.each(this.serverData.data, function(record) {
            probabilities.push(record.probability);
        });

        probabilities = _.unique(probabilities).sort(function(a, b) {
            return b - a
        })

        this.serverData.labels.probability = {};
        _.each(probabilities, function(v) {
            this.serverData.labels.probability[v] = v;
        }, this);
    },


    /**
     * Handler for when the Manager Worksheet Changes
     * @param {Object} model
     */
    mgrWorksheetChanged: function(model) {
        var fieldsChanged = _.keys(model.changed),
            changed = model.changed;

        if (_.contains(fieldsChanged, 'quota')) {
            var q = parseInt(this.serverData.quota, 10);
            q = app.math.add(app.math.sub(q, model.previous('quota')), model.get('quota'));
            this.serverData.quota = q;
        } else {
            var field = _.first(fieldsChanged),
                fieldChartName = field.replace('_case', '');

            // find the user
            _.find(this.serverData.data, function(record, i, list) {
                if (model.get('user_id') == record.user_id) {
                    list[i][fieldChartName] = changed[field];
                    return true;
                }
                return false;
            });
        }

        this.convertDataToChartData();
        this.generateD3Chart();
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
     */
    bindDataChange: function() {
        // on the off chance that the init has not run yet.
        var meta = this.meta || this.initOptions.meta;
        if (meta.config) {
            return;
        }

        this.on('render', function() {
            this.toggleRepOptionsVisibility();
        }, this);

        this.context.parent.on('change:selectedUser', function(context, user) {
            this.values.set({
                user_id: user.id,
                display_manager: (user.showOpps === false && user.isManager === true)
            }, {silent: true});
            this.toggleRepOptionsVisibility();
            this.renderChart();
        }, this);
        this.context.parent.on('change:selectedTimePeriod', function(context, timePeriod) {
            this.values.set({timeperiod_id: timePeriod}, {silent: true});
            this.renderChart();
        }, this);
        this.context.parent.on('change:selectedRanges', function(context, value) {
            this.values.set({ranges: value});
        }, this);
        this.values.on('change', function(value) {
            this.convertDataToChartData();
            this.generateD3Chart();
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
        if (this.forecastManagerWorksheetContext && this.forecastManagerWorksheetContext.get('collection')) {
            this.forecastManagerWorksheetContext.get('collection').off(null, null, this);
        }
        if (this.forecastWorksheetContext && this.forecastWorksheetContext.get('collection')) {
            this.forecastWorksheetContext.get('collection').off(null, null, this);
        }
        if (this.context) this.context.off(null, null, this);
        if (this.values) this.values.off(null, null, this);
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

        var params = this.values.toJSON() || {};
        params.contentEl = 'chart';
        params.minColumnWidth = 120;
        params.type = app.metadata.getModule('Forecasts', 'config').forecast_by;
        params.r = new Date().getTime();

        var url = app.api.buildURL(this.buildChartUrl(params), '', '', data);

        app.api.call('read', url, data, {
            success: _.bind(function(data) {
                this.serverData = data;
                this.convertDataToChartData();
                this.generateD3Chart();
            }, this)
        });
    },

    /**
     * Generate the D3 Chart Object
     */
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

    /**
     * Utility method to determine which data we need to parse,
     */
    convertDataToChartData: function() {
        if (this.values.get('display_manager')) {
            this.convertManagerDataToChartData();
        } else {
            this.convertRepDataToChartData(this.values.get('group_by'))
        }
    },

    /**
     * Parse the Manager Data and set the d3Data object
     */
    convertManagerDataToChartData: function() {
        var dataset = this.values.get('dataset'),
            records = this.serverData.data,
            chartData = {
                'properties': {
                    'name': this.serverData.title,
                    'quota': parseInt(this.serverData.quota, 10),
                    'groupData': records.map(function(record, i) {
                        return {
                            group: i,
                            l: record.name,
                            t: parseInt(record[dataset], 10) + parseInt(record[dataset + '_adjusted'], 10)
                        }
                    })
                },
                'data': []
            },
            barData = [dataset, dataset + '_adjusted'].map(function(ds, seriesIdx) {
                var vals = records.map(function(rec, recIdx) {
                    return {
                        series: seriesIdx,
                        x: recIdx + 1,
                        y: parseInt(rec[ds], 10),
                        y0: 0
                    }
                });

                return {
                    key: this.serverData.labels['dataset'][ds],
                    series: seriesIdx,
                    type: 'bar',
                    values: vals,
                    valuesOrig: vals
                }
            }, this),
            lineData = [dataset, dataset + '_adjusted'].map(function(ds, seriesIdx) {
                var vals = records.map(function(rec, recIdx) {
                    return {
                        series: seriesIdx,
                        x: recIdx + 1,
                        y: parseInt(rec[ds], 10)
                    }
                });

                // fix the vals
                var addToLine = 0;
                _.each(vals, function(val, i, list) {
                    list[i].y += addToLine;
                    addToLine = list[i].y;
                });

                return {
                    key: this.serverData.labels['dataset'][ds],
                    series: seriesIdx,
                    type: 'line',
                    values: vals,
                    valuesOrig: vals
                }
            }, this);

        chartData.data = barData.concat(lineData);
        this.d3Data = chartData;
    },

    /**
     * Convert the Rep Data and set the d3Data Object
     *
     * @param {string} type     What we are dispaying
     */
    convertRepDataToChartData: function(type) {
        var dataset = this.values.get('dataset'),
            ranges = this.values.get('ranges'),
            seriesIdx = 0,
            barData = [],
            lineVals = this.serverData['x-axis'].map(function(axis, i) {
                return { series: seriesIdx, x: i + 1, y: 0 }
            }),
            line = {
                'key': this.serverData.labels.dataset[dataset],
                'type': 'line',
                'series': seriesIdx,
                'values': [],
                'valuesOrig': []
            },
            chartData = {
                'properties': {
                    'name': this.serverData.title,
                    'quota': parseInt(this.serverData.quota, 10),
                    'groupData': this.serverData['x-axis'].map(function(item, i) {
                        return {
                            'group': i,
                            'l': item.label,
                            't': 0
                        }
                    })
                },
                'data': []
            },
            records = this.serverData.data,
            data = (!_.isEmpty(ranges)) ? records.filter(function(rec) {
                return _.contains(ranges, rec.forecast)
            }) : records;

        _.each(this.serverData.labels[type], function(label, value) {
            var td = data.filter(function(d) {
                return (d[type] == value);
            });

            if (!_.isEmpty(td)) {
                var barVal = this.serverData['x-axis'].map(function(axis, i) {
                        return { series: seriesIdx, x: i + 1, y: 0, y0: 0 }
                    }),
                    axis = this.serverData['x-axis'];


                // loop though all the data and map it to the correct x series
                _.each(td, function(record) {
                    for(var y = 0; y < axis.length; y++) {
                        if (record.date_closed_timestamp >= axis[y].start_timestamp &&
                            record.date_closed_timestamp <= axis[y].end_timestamp) {
                            // add the value
                            var val = parseInt(record[dataset], 10);
                            barVal[y].y += val;
                            chartData.properties.groupData[y].t += val;
                            lineVals[y].y += val;
                            break;
                        }
                    }
                }, this);

                barData.push({
                    key: label,
                    series: seriesIdx,
                    type: 'bar',
                    values: barVal,
                    valuesOrig: barVal
                });


                // increase the series
                seriesIdx++;
            }
        }, this);

        if (!_.isEmpty(barData)) {
            // fix the line
            var addToLine = 0;
            _.each(lineVals, function(val, i, list) {
                list[i].y += addToLine;
                addToLine = list[i].y;
            });

            line.values = lineVals;
            line.valuesOrig = lineVals;

            barData.push(line);
            chartData.data = barData
        }

        this.d3Data = chartData;
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
})
