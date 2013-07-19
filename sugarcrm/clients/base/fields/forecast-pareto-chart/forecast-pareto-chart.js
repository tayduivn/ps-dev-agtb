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

    /**
     * @{inheritDoc}
     */
    bindDataChange: function() {
        this.once('render', function() {
            this.renderChart();
        }, this);

        this.model.on('change:user_id change:display_manager', function() {
            this.renderChart();
        }, this);

        this.model.on('change:timeperiod_id', function() {
            this.renderChart();
        }, this);

        this.model.on('change:group_by change:dataset change:ranges', function() {
            this.convertDataToChartData();
            this.generateD3Chart();
        }, this);
    },

    /**
     * Render the chart for the first time
     *
     * @param {Object} [options]        Options from the dashlet loaddata call
     */
    renderChart: function(options) {
        if (this.disposed) {
            return;
        }

        // just on the off chance that no options param is passed in
        options = options || {};
        options.success = _.bind(function(data) {
            this.serverData = data;
            this.convertDataToChartData();
            this.generateD3Chart();
        }, this);

        if (!this.triggerBefore('chart:pareto:render')) {
            return;
        }

        var params = this.model.toJSON() || {},
            url = app.api.buildURL(this.buildChartUrl(params));

        app.api.call('read', url, {}, options);
    },

    /**
     * Generate the D3 Chart Object
     */
    generateD3Chart: function() {
        var params = this.model.toJSON(),
            chartId = this.cid + '_chart',
            paretoChart = nv.models.paretoChart()
                .margin({top: 0, right: 10, bottom: 20, left: 50})
                .showTitle(false)
                .tooltips(true)
                .tooltipLine(function(key, x, y, e, graph) {
                    // Format the value using currency class and user settings
                    var val = App.currency.formatAmountLocale(e.point.y);
                    return '<p>' + key + ': <b>' + val + '</b></p>';
                })
                .tooltipBar(function(key, x, y, e, graph) {
                    // Format the value using currency class and user settings
                    var val = App.currency.formatAmountLocale(e.value);
                    return '<p>' + SUGAR.App.lang.get('LBL_SALES_STAGE', 'Forecasts') + ': <b>' + key + '</b></p>' +
                        '<p>' + SUGAR.App.lang.get('LBL_AMOUNT', 'Forecasts') + ': <b>' + val + '</b></p>' +
                        '<p>' + SUGAR.App.lang.get('LBL_PERCENT', 'Forecasts') + ': <b>' + x + '%</b></p>';
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

        this.trigger('chart:pareto:rendered');
    },

    /**
     * Utility method to determine which data we need to parse,
     */
    convertDataToChartData: function() {
        if (this.model.get('display_manager')) {
            this.convertManagerDataToChartData();
        } else {
            this.convertRepDataToChartData(this.model.get('group_by'));
        }
    },

    /**
     * Parse the Manager Data and set the d3Data object
     */
    convertManagerDataToChartData: function() {
        var dataset = this.model.get('dataset'),
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
                        };
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
                    };
                });

                return {
                    key: this.serverData.labels['dataset'][ds],
                    series: seriesIdx,
                    type: 'bar',
                    values: vals,
                    valuesOrig: vals
                };
            }, this),
            lineData = [dataset, dataset + '_adjusted'].map(function(ds, seriesIdx) {
                var vals = records.map(function(rec, recIdx) {
                    return {
                        series: seriesIdx,
                        x: recIdx + 1,
                        y: parseInt(rec[ds], 10)
                    };
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
                };
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
        var dataset = this.model.get('dataset'),
            ranges = this.model.get('ranges'),
            seriesIdx = 0,
            barData = [],
            lineVals = this.serverData['x-axis'].map(function(axis, i) {
                return { series: seriesIdx, x: i + 1, y: 0 };
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
                        };
                    })
                },
                'data': []
            },
            records = this.serverData.data,
            data = (!_.isEmpty(ranges)) ? records.filter(function(rec) {
                return _.contains(ranges, rec.forecast);
            }) : records;

        _.each(this.serverData.labels[type], function(label, value) {
            var td = data.filter(function(d) {
                return (d[type] == value);
            });

            if (!_.isEmpty(td)) {
                var barVal = this.serverData['x-axis'].map(function(axis, i) {
                        return { series: seriesIdx, x: i + 1, y: 0, y0: 0 };
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
            chartData.data = barData;
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
    },

    /**
     * Return the data that was passed back from the server
     * @returns {Object}
     */
    getServerData: function() {
        return this.serverData;
    },

    /**
     *
     * @param {Object} data
     * @param {Boolean} [adjustLabels]
     */
    setServerData: function(data, adjustLabels) {
        this.serverData = data;

        if (adjustLabels === true) {
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
            return b - a;
        });

        this.serverData.labels.probability = {};
        _.each(probabilities, function(v) {
            this.serverData.labels.probability[v] = v;
        }, this);
    }
})
