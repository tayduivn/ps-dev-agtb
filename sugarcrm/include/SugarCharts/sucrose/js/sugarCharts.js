/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

function loadSugarChart(chartId, jsonFilename, css, chartConfig, chartParams, callback) {
    // get chartId from params or use the default for sugar
    var d3ChartId = 'd3_' + chartId || 'd3_c3090c86-2b12-a65e-967f-51b642ac6165';

    // make sure the chart container exists
    if (document.getElementById(d3ChartId) === null) {
        return false;
    }

    // set barType to 'grouped'
    var chartType = chartConfig.barType || chartConfig.lineType || chartConfig.pieType || chartConfig.funnelType || 'basic';
    var configBarType = chartType === 'stacked' ? 'grouped' : chartType;

    // update default params from chartConfig and then chartParams
    var params = _.extendOwn({
        allowScroll: false,
        base_module: 'Reports',
        barType: configBarType,
        chart_type: 'bar chart',
        colorData: 'default',
        direction: 'ltr',
        groupType: 'basic',
        hideEmptyGroups: true,
        label: SUGAR.charts.translateString('LBL_DASHLET_SAVED_REPORTS_CHART'),
        margin: {top: 10, right: 10, bottom: 10, left: 10},
        module: 'Reports',
        overflowHandler: false,
        reduceXTicks: false,
        rotateTicks: true,
        saved_report_id: chartId,
        show_controls: false,
        show_legend: 'on',
        show_title: true,
        show_tooltips: true,
        show_x_label: false,
        show_y_label: false,
        showValues: false,
        stacked: true,
        staggerTicks: true,
        type: 'saved-report-view',
        vertical: true,
        wrapTicks: true,
        x_axis_label: '',
        y_axis_label: ''
    }, chartConfig, chartParams);
    params.vertical = (chartConfig.orientation ? chartConfig.orientation === 'vertical' : false);

    // chart display strings
    var displayErrorMsg = SUGAR.charts.translateString('LBL_CANNOT_DISPLAY_CHART_MESSAGE', 'Reports');
    var noDataMsg = SUGAR.charts.translateString('LBL_CHART_NO_DATA');
    var legendStrings = {
            close: SUGAR.charts.translateString('LBL_CHART_LEGEND_CLOSE'),
            open: SUGAR.charts.translateString('LBL_CHART_LEGEND_OPEN'),
            noText: SUGAR.charts.translateString('LBL_CHART_UNDEFINED')
        };

    // controls if chart image is auto-saved
    var imageExportType = chartConfig.imageExportType;
    // determines if basic bar chart is displayed as discrete
    var isReportView = chartConfig.ReportModule || false;

    this.chartObject = '';

    // instantiate Sucrose chart
    switch (chartConfig.chartType) {

        case 'barChart':
            SUGAR.charts.get(jsonFilename, params, function(data) {
                var json;
                var barChart;

                if (SUGAR.charts.isDataEmpty(data)) {

                    json = SUGAR.charts.translateDataToD3(data, params, chartConfig);

                    if (json.properties && json.properties.labels && json.properties.labels.length > 50) {
                        SUGAR.charts.renderError(chartId, displayErrorMsg);
                        return;
                    }

                    barChart = sucrose.charts.multibarChart()
                        .id(d3ChartId)
                        .vertical(params.vertical)
                        .margin(params.margin)
                        .showTitle(params.show_title)
                        .tooltips(params.show_tooltips)
                        .tooltipContent(function(eo, properties) {
                            var key = eo.group.label;
                            var seriesKey = eo.series.key;
                            var x = eo.point.x;
                            var y = barChart.valueFormat()(eo.point.y);
                            var p = Math.abs(y * 100 / eo.group._height).toFixed(1);
                            var content = '';
                            var percentString = '';
                            if (x < 100) {
                                percentString = ' - ' + p + '%';
                            }
                            content = '<h3>' + key + '</h3><p>';
                            content += seriesKey && seriesKey.toString().length && key !== seriesKey ?
                                (seriesKey + ': ') :
                                '';
                            content += y + percentString + '</p>';
                            return content;
                        })
                        .direction(params.direction)
                        .showLegend(params.show_legend)
                        .showControls(params.show_controls)
                        .wrapTicks(params.wrapTicks)
                        .staggerTicks(params.staggerTicks)
                        .rotateTicks(params.rotateTicks)
                        .reduceXTicks(params.reduceXTicks)
                        .colorData(params.colorData)
                        .stacked(params.stacked)
                        .allowScroll(params.allowScroll)
                        .overflowHandler(params.overflowHandler)
                        .showValues(params.showValues)
                        .strings({
                            legend: legendStrings,
                            noData: noDataMsg
                        });

                    barChart.textureFill(true);

                    barChart.yAxis.tickSize(0);

                    //check to see if thousands symbol is in use
                    if (
                        typeof data.properties[0] === 'object' &&
                        (typeof data.properties[0].thousands !== 'undefined' &&
                        parseInt(data.properties[0].thousands) === 1)
                    ) {
                        //create formatter with thousands symbol
                        var cFormat = (d3sugar.format('s'));
                        //the tick value comes in shortened from api,
                        //multiply times 1k and apply formatting
                        barChart.yAxis
                            .tickFormat(function(d) {
                                return cFormat(d * 1000);
                            });
                    }

                    if (params.show_x_label) {
                        barChart.xAxis.axisLabel(params.x_axis_label);
                    }

                    if (params.show_y_label) {
                        barChart.yAxis.axisLabel(params.y_axis_label);
                    }

                    if (isReportView) {

                        if (chartConfig.orientation === 'vertical') {
                            barChart.legend.rowsCount(5);
                            barChart.legend.showAll(false);
                        }
                        else {
                            barChart.legend.showAll(true);
                        }

                        SUGAR.charts.trackWindowResize(barChart);

                        if (imageExportType) {
                            SUGAR.charts.saveImageFile(chartId, barChart, json, jsonFilename, imageExportType);
                        } else {
                            SUGAR.charts.renderChart(chartId, barChart, json);
                        }
                    } else {
                        SUGAR.charts.renderChart(chartId, barChart, json);

                        if (params.state) {
                            barChart.cellActivate(params.state);
                        }
                    }

                    SUGAR.charts.callback(callback, barChart, chartId, params);
                }
            });
            break;

        case 'lineChart':
            SUGAR.charts.get(jsonFilename, params, function(data) {
                var json;
                var lineChart;
                var xTickLabels;
                var tickFormat = function(d) { return d; };

                if (SUGAR.charts.isDataEmpty(data)) {

                    json = SUGAR.charts.translateDataToD3(data, params, chartConfig);

                    lineChart = sucrose.charts.lineChart()
                        .id(d3ChartId)
                        .margin(params.margin)
                        .tooltips(params.show_tooltips)
                        .tooltipContent(function(eo, properties) {
                            var key = eo.series.key;
                            var x = eo.point.x;
                            var y = eo.point.y;
                            return '<h3>' + key + '</h3>' +
                                   '<p>' + y + ' on ' + x + '</p>';
                        })
                        .direction(params.direction)
                        .showTitle(params.show_title)
                        .showLegend(params.show_legend)
                        .showControls(params.show_controls)
                        .useVoronoi(true)
                        .clipEdge(false)
                        .wrapTicks(params.wrapTicks)
                        .staggerTicks(params.staggerTicks)
                        .rotateTicks(params.rotateTicks)
                        .colorData(params.colorData)
                        .strings({
                            legend: legendStrings,
                            noData: noDataMsg
                        });

                    if (params.show_x_label) {
                        lineChart.xAxis.axisLabel(params.x_axis_label);
                    }

                    if (params.show_y_label) {
                        lineChart.yAxis.axisLabel(params.y_axis_label);
                    }

                    if (json.data.length) {
                        xTickLabels = json.properties.labels ?
                            json.properties.labels.map(function(d) { return d.l || d; }) :
                            [];

                        if (json.data[0].values.length) {
                            //TODO: date detection is not working because x is index not value
                            // need to pass xDataType from ChartDisplay
                            if (sucrose.utils.isValidDate(json.data[0].values[0].x)) {
                                tickFormat = function(d) { return d3sugar.timeFormat('%x')(new Date(d)); };
                            } else if (xTickLabels.length > 0) {
                                tickFormat = function(d) { return xTickLabels[d - 1] || ' '; };
                            }
                        }
                    }

                    lineChart.xAxis
                        .tickFormat(tickFormat)
                        .highlightZero(false)
                        .reduceXTicks(false);

                    if (isReportView) {
                        lineChart.legend.showAll(true);

                        SUGAR.charts.trackWindowResize(lineChart);

                        if (imageExportType) {
                            SUGAR.charts.saveImageFile(chartId, lineChart, json, jsonFilename, imageExportType);
                        } else {
                            SUGAR.charts.renderChart(chartId, lineChart, json);
                        }
                    } else {
                        SUGAR.charts.renderChart(chartId, lineChart, json);
                    }

                    SUGAR.charts.callback(callback, lineChart, chartId, params);
                }
            });
            break;

        case 'pieChart':
            SUGAR.charts.get(jsonFilename, params, function(data) {
                var json;
                var pieChart;

                if (SUGAR.charts.isDataEmpty(data)) {

                    json = SUGAR.charts.translateDataToD3(data, params, chartConfig);

                    if (json.properties && json.properties.labels && json.properties.labels.length > 50) {
                        SUGAR.charts.renderError(chartId, displayErrorMsg);
                        return;
                    }

                    pieChart = sucrose.charts.pieChart()
                        .id(d3ChartId)
                        .margin(params.margin)
                        .tooltips(params.show_tooltips)
                        .tooltipContent(function(eo, properties) {
                            var key = pieChart.getKey()(eo);
                            var y = pieChart.getValue()(eo);
                            var x = properties.total ? (y * 100 / properties.total).toFixed(1) : 100;
                            return '<h3>' + key + '</h3>' +
                                   '<p>' + y + ' on ' + x + '</p>';
                        })
                        .showTitle(params.show_title)
                        .showLegend(params.show_legend)
                        .colorData(params.colorData)
                        .donut(params.donut || false)
                        .donutLabelsOutside(params.donutLabelsOutside || false)
                        .hole(params.hole || false)
                        .donutRatio(params.donutRatio || 0.5)
                        .rotateDegrees(0)
                        .arcDegrees(360)
                        .fixedRadius(function(chart) {
                            var n = d3sugar.select('#d3_' + chartId).node(),
                                r = Math.min(n.clientWidth * 0.25, n.clientHeight * 0.4);
                            return Math.max(r, 75);
                        })
                        .direction(params.direction)
                        .strings({
                            legend: legendStrings,
                            noData: noDataMsg
                        });

                    pieChart.textureFill(true);

                    if (isReportView) {
                        pieChart.legend.showAll(true);

                        SUGAR.charts.trackWindowResize(pieChart);

                        if (imageExportType) {
                            SUGAR.charts.saveImageFile(chartId, pieChart, json, jsonFilename, imageExportType);
                        } else {
                            SUGAR.charts.renderChart(chartId, pieChart, json);
                        }
                    } else {
                        SUGAR.charts.renderChart(chartId, pieChart, json);

                        if (params.state) {
                            pieChart.seriesActivate(params.state);
                        }
                    }

                    SUGAR.charts.callback(callback, pieChart, chartId, params);
                }
            });
            break;

        case 'funnelChart':
            SUGAR.charts.get(jsonFilename, params, function(data) {
                var json;
                var funnelChart;

                if (SUGAR.charts.isDataEmpty(data)) {

                    json = SUGAR.charts.translateDataToD3(data, params, chartConfig);

                    if (json.properties && json.properties.labels && json.properties.labels.length > 16) {
                        SUGAR.charts.renderError(chartId, displayErrorMsg);
                        return;
                    }

                    funnelChart = sucrose.charts.funnelChart()
                        .id(d3ChartId)
                        .margin(params.margin)
                        .showTitle(params.show_title)
                        .tooltips(params.show_tooltips)
                        .tooltipContent(function(eo, properties) {
                            var key = funnelChart.fmtKey()(eo);
                            var y = funnelChart.getValue()(eo);
                            var x = properties.total ? (y * 100 / properties.total).toFixed(1) : 100;
                            return '<h3>' + key + '</h3>' +
                                   '<p>' + y + ' on ' + x + '</p>';
                        })
                        .direction(params.direction)
                        .colorData(params.colorData)
                        .fmtValue(function(d) {
                            return d.label || d.value || d;
                        })
                        .strings({
                            legend: legendStrings,
                            noData: noDataMsg
                        });

                    funnelChart.textureFill(true);

                    if (isReportView) {
                        funnelChart.legend.showAll(true);

                        SUGAR.charts.trackWindowResize(funnelChart, chartId, data);

                        if (imageExportType) {
                            SUGAR.charts.saveImageFile(chartId, funnelChart, json, jsonFilename, imageExportType);
                        } else {
                            SUGAR.charts.renderChart(chartId, funnelChart, json);
                        }
                    } else {
                        SUGAR.charts.renderChart(chartId, funnelChart, json);

                        if (params.state) {
                            funnelChart.seriesActivate(params.state);
                        }
                    }

                    SUGAR.charts.callback(callback, funnelChart, chartId, params);
                }
            });
            break;

        case 'gaugeChart':
            SUGAR.charts.get(jsonFilename, params, function(data) {
                var json;
                var maxValue;
                var gaugeChart;

                if (SUGAR.charts.isDataEmpty(data)) {
                    json = SUGAR.charts.translateDataToD3(data, params, chartConfig);
                    maxValue = d3sugar.max(json.data.map(function(d) { return d.y; }));

                    if (maxValue === 0) {
                        json.data[0].y = 1;
                        maxValue = 1;
                    }

                    json.data.map(function(d, i) {
                        d.classes = 'sc-fill0' + (i + 1);
                    });

                    //init Gauge Chart
                    gaugeChart = sucrose.charts.gaugeChart()
                        .id(d3ChartId)
                        .x(function(d) { return d.key; })
                        .y(function(d) { return d.y; })
                        .direction(params.direction)
                        .showLabels(true)
                        .showTitle(true)
                        .colorData('class')
                        .ringWidth(50)
                        .maxValue(maxValue)
                        .transitionMs(4000);

                    if (isReportView) {
                        gaugeChart.legend.showAll(true);

                        SUGAR.charts.trackWindowResize(gaugeChart);

                        if (imageExportType) {
                            SUGAR.charts.saveImageFile(chartId, gaugeChart, json, jsonFilename, imageExportType);
                        } else {
                            SUGAR.charts.renderChart(chartId, gaugeChart, json);
                        }
                    } else {
                        SUGAR.charts.renderChart(chartId, gaugeChart, json);
                    }

                    SUGAR.charts.callback(callback, gaugeChart, chartId, params);
                }
            });
            break;
    }
}

/**
 * Global sugar chart class
 */
(function($) {
    if (typeof SUGAR == 'undefined' || !SUGAR) {
        SUGAR = {};
    }

    SUGAR.charts = {
        chart: null,
        chart_loaded: false,

        /**
         * Execute callback function if specified
         *
         * @param callback function to invoke after chart rendering
         * @param chart Sucrose chart instance to render
         * @param chartId chart id used to select the chart container
         * @param params chart display control parameters
         */
        callback: function(callback, chart, chartId, params) {
            // add drill through support
            this.chart = chart;
            this.chart_loaded = _.isFunction(chart.update);

            if (!this.chart_loaded) {
                return;
            }

            if (callback) {
                // if the call back is provided, include the chart as the only param
                callback(chart);
                return;
            }

            // only assign the event handler if chart supports it
            if (!_.isFunction(chart.seriesClick)) {
                return;
            }

            // This default seriesClick callback is normally used
            // by the Report module charts. Saved Reports Chart
            // dashlets override with their own handler
            chart.seriesClick(_.bind(function(data, eo, chart, labels) {
                var chartState = this.buildChartState(eo, labels);
                var groupDefs;
                var filterDef;

                params.seriesLabel = this.extractSeriesLabel(eo, data);
                params.groupLabel = this.extractGroupLabel(eo, labels);

                // report_def is defined as a global in _reportCriteriaWithResult
                // but only in Reports module
                // var base_module = params.base_module || (_.isUndefined(report_def) ? 'Home' : report_def);
                //TODO: fix usage of global report_def
                groupDefs = this.getGrouping(report_def);
                filterDef = this.buildFilter(report_def, params);

                chart.clearActive();
                if (chart.cellActivate) {
                    chart.cellActivate(chartState);
                } else if (chart.seriesActivate) {
                    chart.seriesActivate(chartState);
                } else {
                    chart.dataSeriesActivate(eo);
                }
                chart.dispatch.call('tooltipHide', this);

                // No need for _handleFilter since we alwasy open drawer in Reports module
                // this._handleFilter(filterDef, chartData, chartState);
                app.alert.show('listfromreport_loading', {level: 'process', title: app.lang.get('LBL_LOADING')});
                this.chart.clearActive();
                this.openDrawer(report_def.module, chartId, groupDefs, filterDef, chartState, params);
            }, this));
        },

        /**
         * Create an active state object based on chart element clicked
         *
         * @param eo an event object with extended properties
         * constructed from a clicked chart element
         * @param labels an array of grouping labels
         */
        buildChartState: function(eo, labels) {
            var seriesIndex = eo.seriesIndex || eo.series.seriesIndex;
            if (_.isEmpty(labels)) {
                return {seriesIndex: seriesIndex || eo.pointIndex};
            } else {
                return {seriesIndex: seriesIndex, groupIndex: eo.pointIndex};
            }
        },

        /**
         * Get the series label from chart data based on chart element clicked
         *
         * @param eo an event object with extended properties
         * constructed from a clicked chart element
         * @param data report data
         */
        extractSeriesLabel: function(eo, data) {
            var seriesIndex = eo.seriesIndex || eo.series.seriesIndex;
            return _.isUndefined(seriesIndex) ? data[eo.pointIndex].key : data[seriesIndex].key;
        },

        /**
         * Get the group label from chart labels based on chart element clicked
         *
         * @param eo an event object with extended properties
         * constructed from a clicked chart element
         * @param labels an array of grouping labels
         */
        extractGroupLabel: function(eo, labels) {
            return _.isEmpty(labels) ? null : labels[eo.pointIndex];
        },

        /**
         * Get the first or second grouping from report definition
         * or the first grouping if there is only one
         *
         * @param reportDef report definition object
         * @param i group definition index
         * @return {object}
         */
        getGrouping: function(reportDef, i) {
            var groupDefs = reportDef.group_defs;
            if (isNaN(i)) {
                return groupDefs;
            }
            return i > 0 && groupDefs.length > 1 ? reportDef.group_defs[1] : reportDef.group_defs[0];
        },

        /**
         * Construct a new report definition filter
         *
         * @param reportDef report definition object
         * @param params chart display control parameters
         * @return {array}
         */
        buildFilter: function(reportDef, params) {
            var def = [];
            var mode = '$in';

            var groups = this.getGrouping(reportDef, 0);
            var series = this.getGrouping(reportDef, 1);

            var isGroupType = params.groupType === 'grouped';
            var groupLabel = params.groupLabel;
            var seriesLabel = params.seriesLabel;

            var hasSameLabel = !_.isEmpty(seriesLabel) && !_.isEmpty(groupLabel) && seriesLabel === groupLabel;
            var hasSameGroup = groups.name === series.name &&
                               groups.label === series.label &&
                               groups.table_key === series.table_key;

            var groupsValues = [];
            var seriesValues = [];

            function setValues(values, label) {
                mode = '$in';
                values.push(label);
            }

            function addFilterRow(name, values) {
                var field = {};
                var row = {};
                field[mode] = values;
                row[name] = field;
                def.push(row);
            }

            function addSeriesRow() {
                setValues(seriesValues, seriesLabel, series);
                addFilterRow(series.name, seriesValues);
            }

            function addGroupRow() {
                setValues(groupsValues, groupLabel, groups);
                addFilterRow(groups.name, groupsValues);
            }

            // pie & funnel chart
            if (!isGroupType && hasSameGroup && !_.isEmpty(seriesLabel) && _.isEmpty(groupLabel)) {
                // then use series
                groupLabel = groupLabel || seriesLabel;
                params.groupLabel = groupLabel;
                addSeriesRow();
            }
            // pie & funnel chart & grouped data
            // this happens when data with multiple groupings is displayed as pie or funnel
            else if (!isGroupType && !hasSameGroup && !hasSameLabel) {
                // then use group
                groupLabel = groupLabel || seriesLabel;
                params.groupLabel = groupLabel;
                addGroupRow();
            }
            // grouped or basic type & discrete data (isGroupType ignored)
            else if (hasSameGroup && hasSameLabel) {
                // then use either, but only one
                addSeriesRow();
            }
            // grouped type & grouped data
            else if (isGroupType && !hasSameGroup && !hasSameLabel) {
                // then use both
                addGroupRow();
                addSeriesRow();
            }
            // basic type & discrete data
            else if (!isGroupType && hasSameGroup && !hasSameLabel) {
                // then use group
                addGroupRow();
            }
            // basic type & grouped data
            else if (!isGroupType && !hasSameGroup && hasSameLabel) {
                // then use group
                addGroupRow();
            }

            return def;
        },

        /**
         * Open a drill through drawer
         */
        openDrawer: function(chartModule, reportId, groupDefs, filterDef, chartState, dashConfig) {
            var sugarApp = SUGAR.App || SUGAR.app || app;
            sugarApp.drawer.open({
                layout: 'drillthrough-drawer',
                context: {
                    layout: 'drillthrough-drawer',
                    module: chartModule,
                    chartModule: chartModule,
                    chartState: chartState,
                    reportId: reportId,
                    filterOptions: {
                        auto_apply: false
                    },
                    filterDef: filterDef,
                    groupDefs: groupDefs,
                    skipFetch: true,
                    dashModel: null,
                    dashConfig: dashConfig
                }
            });
        },

        /**
         * Main render chart method
         *
         * @param id chart id used to select the chart container
         * @param chart Sucrose chart instance to render
         * @param json report data to render
         */
        renderChart: function(id, chart, json) {
            $('#d3_' + id).empty();
            d3sugar.select('#d3_' + id)
                .append('svg')
                .attr('class', 'sucrose sc-chart')
                .datum(json)
                .transition().duration(500)
                .call(chart);
        },

        /**
         * Display an error message in chart container
         *
         * @param id chart id used to select the chart container
         * @param str error message string
         */
        renderError: function(id, str) {
            $('#d3_' + id).empty();
            d3sugar.select('.reportChartContainer')
                .style('height', 'auto');
            d3sugar.select('.reportChartContainer .chartContainer')
                .style('float', 'none')
                .style('position', 'relative')
                .style('width', '100%');
            d3sugar.select('#d3_' + id)
                .style('height', 'auto')
                .append('div')
                    .attr('class', 'sc-data-error')
                    .attr('align', 'center')
                    .style('padding', '12px')
                    .text(str);
        },

        /**
         * Calls the server to retrieve chart data, but
         * For D3 charts we already have the data, don't need to make an ajax call to get anything
         * so this is now a polymorphic method.
         *
         * @param urlordata - JSON data for the chart field or target url for Reports module
         * @param param - object of parameters to pass to the server
         * @param success - callback function to be executed after a successful call
         */
        get: function(urlordata, params, success) {
            if (typeof urlordata === 'string') {
                var data = {
                    r: new Date().getTime()
                };
                $.extend(data, params);
                $.ajax({
                    url: urlordata,
                    data: data,
                    dataType: 'json',
                    async: false,
                    success: success
                });
            } else {
                success(urlordata);
            }
        },

        /**
         * Translate a chart string using current application language
         *
         * @param appString string to translate
         * @param module module where the string is defined
         * @return {string}
         */
        translateString: function(appString, module) {
            if (SUGAR.language) {
                if (module) {
                    return SUGAR.language.get(module, appString);
                } else {
                    return SUGAR.language.get('app_strings', appString);
                }
            } else if (SUGAR.App) {
                if (module) {
                    return SUGAR.App.lang.get(appString, module);
                } else {
                    return SUGAR.App.lang.get(appString);
                }
            } else {
                return appString;
            }
        },

        /**
         * Transform data from Report module to format used by Sucrose
         *
         * @param json the report data to transform
         * @param params chart display control parameters
         * @param config chart configuration settings
         * @return {object} contains chart properties object and data array
         */
        translateDataToD3: function(json, params, config) {
            var data = [];
            var value = 0;
            var properties = json.properties[0] || {};
            var strUndefined = SUGAR.charts.translateString('LBL_CHART_UNDEFINED');
            var hasValues = json.values.filter(function(d) {
                    return Array.isArray(d.values) && d.values.length;
                }).length;
            var isGroupedBarType;
            var isDiscreteData = hasValues && Array.isArray(json.label) &&
                    json.label.length === json.values.length &&
                    json.values.reduce(function(a, b) {
                        return a && Array.isArray(b.values) && b.values.length === 1;
                    }, true);

            function sumValues(values) {
                // 0 is default value if reducing an empty list
                return values.reduce(function(a, b) { return parseFloat(a) + parseFloat(b); }, 0);
            }

            function pickLabel(label) {
                var l = [].concat(label)[0];
                return l ? l : strUndefined;
            }

            if (hasValues) {
                switch (config.chartType) {

                    case 'barChart':
                        if ((config.ReportModule && isDiscreteData) || config.barType === 'stacked') {
                            params.barType = config.barType = 'grouped';
                        }
                        isGroupedBarType = params.barType === 'grouped';

                        data = isGroupedBarType && !isDiscreteData ?
                            // is grouped bar type on grouped data
                            json.label.map(function(d, i) {
                                return {
                                    'key': pickLabel(d),
                                    'type': 'bar',
                                    'values': json.values.map(function(e, j) {
                                        return {
                                            'series': i,
                                            'x': j + 1,
                                            'y': parseFloat(e.values[i]) || 0,
                                            'y0': 0
                                        };
                                    })
                                };
                            }) :
                            (isGroupedBarType && isDiscreteData) || (!isGroupedBarType && !isDiscreteData) ?
                                // is grouped bar type on discrete data OR basic bar type on grouped data
                                json.values.map(function(d, i) {
                                    return {
                                        'key': d.values.length > 1 ? d.label : pickLabel(d.label),
                                        'type': 'bar',
                                        'values': json.values.map(function(e, j) {
                                            return {
                                                'series': i,
                                                'x': j + 1,
                                                'y': i === j ? sumValues(e.values) : 0,
                                                'y0': 0
                                            };
                                        })
                                    };
                                }) :
                                // is basic bar type on discrete data
                                [{
                                    'key': params.module || properties.base_module,
                                    'type': 'bar',
                                    'values': json.values.map(function(e, j) {
                                        return {
                                            'series': j,
                                            'x': j + 1,
                                            'y': sumValues(e.values),
                                            'y0': 0
                                        };
                                    })
                                }];

                        break;

                    case 'pieChart':
                        data = json.values.map(function(d, i) {
                            var data = {
                                'key': pickLabel(d.label),
                                'value': sumValues(d.values)
                            };
                            if (d.color !== undefined) {
                                data.color = d.color;
                            }
                            if (d.classes !== undefined) {
                                data.classes = d.classes;
                            }
                            return data;
                        });
                        break;

                    case 'funnelChart':
                        data = json.values.reverse().map(function(d, i) {
                            return {
                                'key': pickLabel(d.label),
                                'values': [{
                                    'series': i,
                                    'label': d.valuelabels[0] ? d.valuelabels[0] : d.values[0],
                                    'x': 0,
                                    'y': sumValues(d.values),
                                    'y0': 0
                                }]
                            };
                        });
                        break;

                    case 'lineChart':
                        data = json.values.map(function(d, i) {
                            return {
                                'key': pickLabel(d.label),
                                'values': isDiscreteData ?
                                    d.values.map(function(e, j) {
                                        return {x: i + 1, y: parseFloat(e)};
                                    }) :
                                    d.values.map(function(e, j) {
                                        return {x: j + 1, y: parseFloat(e)};
                                    })
                            };
                        });
                        break;

                    case 'gaugeChart':
                        value = json.values.shift().gvalue;
                        var y0 = 0;

                        data = json.values.map(function(d, i) {
                            var values = {
                                'key': pickLabel(d.label),
                                'y': parseFloat(d.values[0]) + y0
                            };
                            y0 += parseFloat(d.values[0]);
                            return values;
                        });
                        break;
                }
            }

            return {
                'properties': {
                    'title': properties.title,
                    // bar group data (x-axis)
                    'groups': config.chartType === 'lineChart' && json.label ?
                        json.label.map(function(d, i) {
                            return {
                                'group': i + 1,
                                'label': pickLabel(d)
                            };
                        }) :
                        hasValues ?
                            json.values.map(function(d, i) {
                                return {
                                    'group': i + 1,
                                    'label': pickLabel(d.label)
                                };
                            }) :
                            [],
                    'values': config.chartType === 'gaugeChart' ?
                        [{'group': 1, 'total': value}] :
                        hasValues ?
                            json.values.map(function(d, i) {
                                return {
                                    'group': i + 1,
                                    'total': sumValues(d.values)
                                };
                            }) :
                            []
                },
                // series data
                'data': data
            };
        },

        /**
         * Is data returned from the server empty?
         *
         * @param data
         * @return {boolean}
         */
        isDataEmpty: function(data) {
            if (data !== undefined && data !== 'No Data' && data !== '') {
                return true;
            } else {
                return false;
            }
        },

        /**
         * Resize graph on window resize
         *
         * @param chart Sucrose chart instance to render
         */
        trackWindowResize: function(chart) {
            // var resizer = chart.render ? chart.render : chart.update;
            $(window).on('resize.' + this.sfId, _.debounce(_.bind(function() {
                if (chart.render) {
                    chart.render();
                } else {
                    chart.update();
                }
            }, this), 300));
        },

        /**
         * Save the current chart to an image
         *
         * @param id chart id used to construct the chart container id
         * @param chart Sucrose chart instance to call
         * @param json report data to render
         * @param jsonfilename name of the data file to save image as
         * @param imageExt type of image to save
         * @param saveTo url of service to post the image to
         * @param complete the callback to reset chart instance after saving image
         */
        saveImageFile: function(id, chart, json, jsonfilename, imageExt, saveTo, complete) {
            var d3ChartId = '#d3_' + id + '_print' || 'd3_c3090c86-2b12-a65e-967f-51b642ac6165_print';
            var canvasChartId = 'canvas_' + id || 'canvas_c3090c86-2b12-a65e-967f-51b642ac6165';
            var svgChartId = 'svg_' + id || 'canvas_c3090c86-2b12-a65e-967f-51b642ac6165';
            var legendShowState = chart.legend.showAll();
            var textureFillState = true;

            var completeCallback = complete || _.bind(function() {
                chart.legend.showAll(legendShowState); //restore showAll state for web render
                // reenable texture fill for onclick feedback
                if (chart.textureFill) {
                    chart.textureFill(textureFillState);
                }
                // now that image is generated
                // it is ok to render the visible chart
                this.renderChart(id, chart, json);
            }, this);

            chart.legend.showAll(true); //set showAll legend property for images

            // temporarily turn off texture filling for onclick feedback
            if (chart.textureFill) {
                textureFillState = chart.textureFill()
                chart.textureFill(false);
            }

            d3sugar.select(d3ChartId + ' svg').remove();

            d3sugar.select(d3ChartId)
                .append('svg')
                .attr('class', 'sucrose sc-chart')
                .attr('id', svgChartId)
                .datum(json)
                .call(chart);

            d3sugar.select(d3ChartId).selectAll('.sc-axis line')
              .style('stroke', '#DDD')
              .style('stroke-width', 1)
              .style('stroke-opacity', 1);

            var parts = jsonfilename.split('/');
            var filename = parts[parts.length - 1].replace('.js', '.' + imageExt);
            var oCanvas = document.getElementById(canvasChartId);
            var d3Container = document.getElementById(svgChartId);
            var serializer = new XMLSerializer();
            var saveToUrl = saveTo || 'index.php?action=DynamicAction&DynamicAction=saveImage&module=Charts&to_pdf=1';

            if (!oCanvas) {
                return;
            }

            $.ajax({
                url: 'styleguide/assets/css/sucrose_print.css',
                dataType: 'text',
                success: function(css) {
                    var canvgOptions = {
                            ignoreMouse: true,
                            ignoreAnimation: false,
                            ignoreClear: true,
                            ignoreDimensions: true,
                            scaleWidth: 1440,
                            scaleHeight: 960,
                            renderCallback: function() {
                                var uri = oCanvas.toDataURL((imageExt === 'jpg' ? 'image/jpeg' : 'image/png'));
                                var ctx = oCanvas.getContext('2d');

                                $.post(saveToUrl, {imageStr: uri, filename: filename});

                                ctx.clearRect(0, 0, 1440, 960);

                                completeCallback();
                            }
                        };

                    setTimeout(function() {
                        var svg = serializer.serializeToString(d3Container);
                        var svgAttr = ' xmlns:xlink="http://www.w3.org/1999/xlink" width="720"' +
                                      ' height="480" viewBox="0 0 1440 960">';
                        var cssCdata = '<style type="text/css"><![CDATA[' + css.trim() + ']]></style>';
                        var d3Chart = svg.replace(
                                /><g class="sc-chart-wrap/,
                                (svgAttr + cssCdata + '<g class="sc-chart-wrap')
                            );

                        canvg(canvasChartId, d3Chart, canvgOptions);
                    }, 1000);
                }
            });
        }
    };
})(jQuery);
