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

function loadSugarChart(chartId, jsonFilename, css, chartConfig, params, callback) {
    this.chartObject = '';

    // get chartId from params or use the default for sugar
    var d3ChartId = 'd3_' + chartId || 'd3_c3090c86-2b12-a65e-967f-51b642ac6165';
    var canvasChartId = 'canvas_' + chartId || 'canvas_c3090c86-2b12-a65e-967f-51b642ac6165';

    if (document.getElementById(d3ChartId) === null) {
        return false;
    }

    var labelType = 'Native',
        useGradients = false,
        animate = false,
        that = this,
        /**
         * the main container to render chart
         */
        contentEl = 'content',
        /**
         * width of one column to render bars
         */
        minColumnWidth = 40;

    params = _.extend({
        show_title: true,
        show_legend: true,
        show_controls: false,
        show_tooltips: true,
        show_y_label: false,
        y_axis_label: '',
        show_x_label: false,
        x_axis_label: '',
        rotateTicks: 0,
        staggerTicks: false,
        reduceXTicks: false,
        colorData: 'default',
        margin: {top: 10, right: 10, bottom: 10, left: 10}
    }, params);

    contentEl = params.contentEl || contentEl;
    minColumnWidth = params.minColumnWidth || minColumnWidth;

    switch (chartConfig['chartType']) {

        case 'paretoChart':
            SUGAR.charts.get(jsonFilename, params, function(data) {

                if (SUGAR.charts.isDataEmpty(data)) {
                    var json = SUGAR.charts.translateParetoDataToD3(data, params, chartConfig);

                    var marginBottom = (chartConfig['orientation'] == 'vertical' && data.values.length > 8) ? 20 * 4 : 20;

                    var paretoChart = nv.models.paretoChart()
                        .margin(params.margin)
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
                            return '<p>' + SUGAR.charts.translateString('LBL_SALES_STAGE', 'Forecasts') + ': <b>' + key + '</b></p>' +
                                   '<p>' + SUGAR.charts.translateString('LBL_AMOUNT', 'Forecasts') + ': <b>' + val + '</b></p>' +
                                   '<p>' + SUGAR.charts.translateString('LBL_PERCENT', 'Forecasts') + ': <b>' + x + '%</b></p>';
                        })
                        .showControls(false)
                        .colorData('default')
                        .colorFill('default')
                        .stacked(!params.display_manager)
                        .id(chartId)
                        .strings({
                            legend: {
                                close: SUGAR.charts.translateString('LBL_CHART_LEGEND_CLOSE'),
                                open: SUGAR.charts.translateString('LBL_CHART_LEGEND_OPEN')
                            },
                            noData: SUGAR.charts.translateString('LBL_CHART_NO_DATA')
                        });

                    // get chartId from params or use the default for sugar
                    d3ChartId = params.chartId || 'db620e51-8350-c596-06d1-4f866bfcfd5b';

                    var completeCallback = function() {
                        SUGAR.charts.renderChart(chartId);
                        d3.select('#' + d3ChartId)
                            .selectAll('.nv-y.nv-axis text')
                            .text(function(d) {
                                return App.user.get('preferences').currency_symbol + d3.format(',.2s')(d);
                            });
                    };

                    that.chartObject = paretoChart;

                    SUGAR.charts.setChartObject(paretoChart);
                    SUGAR.charts.setChartData(json);

                    if (chartConfig['ReportModule']) {
                        paretoChart.legend
                            .showAll(true);

                        SUGAR.charts.trackWindowResize(paretoChart, chartId, data);

                        if (chartConfig['imageExportType']) {
                            SUGAR.charts.saveImageFile(chartId, jsonFilename, chartConfig['imageExportType'], completeCallback);
                        } else {
                            SUGAR.charts.renderChart(chartId);
                        }
                    } else {
                        // After the .call(paretoChart) line, we are selecting the text elements for the Y-Axis
                        // only so we can custom format the Y-Axis values
                        d3.select('#' + d3ChartId)
                            .append('svg')
                            .datum(json)
                            .transition().duration(500)
                            .call(paretoChart)
                            .selectAll('.nv-y.nv-axis text')
                            .text(function(d) {
                                return App.user.get('preferences').currency_symbol + d3.format(',.2s')(d);
                            });
                    }
                }
                SUGAR.charts.callback(callback);
            });
            break;

        case 'barChart':
            SUGAR.charts.get(jsonFilename, params, function(data) {

                if (SUGAR.charts.isDataEmpty(data)) {
                    var json = SUGAR.charts.translateDataToD3(data, params, chartConfig);

                    var barChart = (chartConfig['orientation'] === 'vertical') ? nv.models.multiBarChart() : nv.models.multiBarHorizontalChart();

                    barChart
                        .id(d3ChartId)
                        .margin(params.margin)
                        .showTitle(params.show_title)
                        .tooltips(params.show_tooltips)
                        .tooltipContent(function(key, x, y, e, graph) {
                            return '<h3>' + key + '</h3>' +
                                '<p>' + y + '</p>';
                        })
                        .showLegend(params.show_legend)
                        .showControls(params.show_controls)
                        .rotateTicks(params.rotateTicks)
                        .reduceXTicks(params.reduceXTicks)
                        .colorData(params.colorData)
                        .stacked(chartConfig.barType === 'stacked' ? true : true)
                        .strings({
                            legend: {
                                close: SUGAR.charts.translateString('LBL_CHART_LEGEND_CLOSE'),
                                open: SUGAR.charts.translateString('LBL_CHART_LEGEND_OPEN')
                            },
                            noData: SUGAR.charts.translateString('LBL_CHART_NO_DATA')
                        });

                    barChart.yAxis
                        .tickSize(0)
                        .axisLabel(params.show_y_label)
                        .tickFormat(d3.format(',.0f'));

                    if (params.show_x_label) {
                        barChart.xAxis
                            .axisLabel(params.x_axis_label);
                    }

                    if (params.show_y_label) {
                        barChart.yAxis
                            .axisLabel(params.y_axis_label);
                    }

                    that.chartObject = barChart;

                    SUGAR.charts.setChartObject(barChart);
                    SUGAR.charts.setChartData(json);

                    if (chartConfig['ReportModule']) {
                        barChart.legend
                            .showAll(true);

                        SUGAR.charts.trackWindowResize(barChart, chartId, data);

                        if (chartConfig['imageExportType']) {
                            SUGAR.charts.saveImageFile(chartId, jsonFilename, chartConfig['imageExportType']);
                        } else {
                            SUGAR.charts.renderChart(chartId);
                        }
                    } else {
                        SUGAR.charts.renderChart(chartId);
                    }

                }
                SUGAR.charts.callback(callback);
            });
            break;

        case 'lineChart':
            SUGAR.charts.get(jsonFilename, params, function(data) {
                if (SUGAR.charts.isDataEmpty(data)) {
                    var json = SUGAR.charts.translateDataToD3(data, params, chartConfig);
                    var xLabels = data.label;

                    var lineChart = nv.models.lineChart()
                        .id(d3ChartId)
                        .x(function(d) { return d[0]; })
                        .y(function(d) { return d[1]; })
                        .size(function() { return 123; })
                        .margin(params.margin)
                        .tooltips(params.show_tooltips)
                        .tooltipContent(function(key, x, y, e, graph) {
                            return '<h3>' + key + '</h3>' +
                                '<p>' + y + '</p>';
                        })
                        .showTitle(params.show_title)
                        .showLegend(params.show_legend)
                        .showControls(params.show_controls)
                        .colorData(params.colorData)
                        .strings({
                            legend: {
                                close: SUGAR.charts.translateString('LBL_CHART_LEGEND_CLOSE'),
                                open: SUGAR.charts.translateString('LBL_CHART_LEGEND_OPEN')
                            },
                            noData: SUGAR.charts.translateString('LBL_CHART_NO_DATA')
                        });

                    if (params.show_x_label) {
                        lineChart.xAxis
                            .axisLabel(params.x_axis_label);
                    }

                    if (params.show_y_label) {
                        lineChart.yAxis
                            .axisLabel(params.y_axis_label);
                    }

                    lineChart.xAxis
                        .showMaxMin(false)
                        .highlightZero(false)
                        .axisLabel(params.show_x_label)
                        .tickFormat(function(d, i) { return xLabels[d]; });

                    that.chartObject = lineChart;

                    SUGAR.charts.setChartObject(lineChart);
                    SUGAR.charts.setChartData(json);

                    if (chartConfig['ReportModule']) {
                        lineChart.legend
                            .showAll(true);

                        SUGAR.charts.trackWindowResize(lineChart, chartId, data);

                        if (chartConfig['imageExportType']) {
                            SUGAR.charts.saveImageFile(chartId, jsonFilename, chartConfig['imageExportType']);
                        } else {
                            SUGAR.charts.renderChart(chartId);
                        }
                    } else {
                        SUGAR.charts.renderChart(chartId);
                    }
                }
                SUGAR.charts.callback(callback);
            });
            break;

        case 'pieChart':
            SUGAR.charts.get(jsonFilename, params, function(data) {
                if (SUGAR.charts.isDataEmpty(data)) {
                    var json = SUGAR.charts.translateDataToD3(data, params, chartConfig);

                    var pieChart = nv.models.pieChart()
                        .id(d3ChartId)
                        .margin(params.margin)
                        .tooltips(params.show_tooltips)
                        .showTitle(params.show_title)
                        .showLegend(params.show_legend)
                        .colorData(params.colorData)
                        .strings({
                            legend: {
                                close: SUGAR.charts.translateString('LBL_CHART_LEGEND_CLOSE'),
                                open: SUGAR.charts.translateString('LBL_CHART_LEGEND_OPEN')
                            },
                            noData: SUGAR.charts.translateString('LBL_CHART_NO_DATA')
                        });

                    that.chartObject = pieChart;

                    SUGAR.charts.setChartObject(pieChart);
                    SUGAR.charts.setChartData(json);

                    if (chartConfig['ReportModule']) {
                        pieChart.legend
                            .showAll(true);

                        SUGAR.charts.trackWindowResize(pieChart, chartId, data);

                        if (chartConfig['imageExportType']) {
                            SUGAR.charts.saveImageFile(chartId, jsonFilename, chartConfig['imageExportType']);
                        } else {
                            SUGAR.charts.renderChart(chartId);
                        }
                    } else {
                        SUGAR.charts.renderChart(chartId);
                    }
                }
                SUGAR.charts.callback(callback);
            });
            break;

        case 'funnelChart':
            SUGAR.charts.get(jsonFilename, params, function(data) {
                if (SUGAR.charts.isDataEmpty(data)) {
                    var json = SUGAR.charts.translateDataToD3(data, params, chartConfig);

                    var funnelChart = nv.models.funnelChart()
                        .id(d3ChartId)
                        .margin(params.margin)
                        .showTitle(params.show_title)
                        .tooltips(params.show_tooltips)
                        .tooltipContent(function(key, x, y, e, graph) {
                            return '<h3>' + key + '</h3>' +
                                '<p>' + e.value + '</p>';
                        })
                        .colorData(params.colorData)
                        .fmtValueLabel(function(d) {
                            return d.value;
                        })
                        .clipEdge(false)
                        .delay(1)
                        .strings({
                            legend: {
                                close: SUGAR.charts.translateString('LBL_CHART_LEGEND_CLOSE'),
                                open: SUGAR.charts.translateString('LBL_CHART_LEGEND_OPEN')
                            },
                            noData: SUGAR.charts.translateString('LBL_CHART_NO_DATA')
                        });

                    that.chartObject = funnelChart;

                    SUGAR.charts.setChartObject(funnelChart);
                    SUGAR.charts.setChartData(json);

                    if (chartConfig['ReportModule']) {
                        funnelChart.legend
                            .showAll(true);

                        SUGAR.charts.trackWindowResize(funnelChart, chartId, data);

                        if (chartConfig['imageExportType']) {
                            SUGAR.charts.saveImageFile(chartId, jsonFilename, chartConfig['imageExportType']);
                        } else {
                            SUGAR.charts.renderChart(chartId);
                        }
                    } else {
                        SUGAR.charts.renderChart(chartId);
                    }
                }
                SUGAR.charts.callback(callback);
            });
            break;

        case 'gaugeChart':
            SUGAR.charts.get(jsonFilename, params, function(data) {
                if (SUGAR.charts.isDataEmpty(data)) {
                    var properties = $jit.util.splat(data.properties)[0];

                    //init Gauge Chart
                    var gaugeChart = new $jit.GaugeChart({
                        //id of the visualization container
                        injectInto: chartId,
                        //whether to add animations
                        animate: animate,
                        renderBackground: chartConfig['imageExportType'] == 'jpg' ? true : false,
                        backgroundColor: 'rgb(255,255,255)',
                        colorStop1: 'rgba(255,255,255,.8)',
                        colorStop2: 'rgba(255,255,255,0)',
                        labelType: properties['labels'],
                        hoveredColor: false,
                        Title: {
                            text: properties['title'],
                            size: 16,
                            color: '#444444',
                            offset: 20
                        },
                        Subtitle: {
                            text: properties['subtitle'],
                            size: 11,
                            color: css['color'],
                            offset: 5
                        },
                        //offsets
                        offset: 20,
                        gaugeStyle: {
                            backgroundColor: '#aaaaaa',
                            borderColor: '#999999',
                            needleColor: 'rgba(255,0,0,.8)',
                            borderSize: 4,
                            positionFontSize: 24,
                            positionOffset: 2
                        },
                        //slice style
                        type: useGradients ? chartConfig['gaugeType'] + ':gradient' : chartConfig['gaugeType'],
                        //whether to show the labels for the slices
                        showLabels: true,
                        Events: {
                            enable: true,
                            onClick: function(node) {
                                if (!node || $jit.util.isTouchScreen()) return;
                                if (node.link == 'undefined' || node.link === '') return;
                                window.location.href = node.link;
                            }
                        },
                        //label styling
                        Label: {
                            type: labelType, //Native or HTML
                            size: 12,
                            family: css['font-family'],
                            color: css['color']
                        },
                        //enable tips
                        Tips: {
                            enable: true,
                            onShow: function(tip, elem) {
                                if (elem.link !== 'undefined' && elem.link !== '') {
                                    drillDown = ($jit.util.isTouchScreen()) ? '<br><a href="' + elem.link + '">Click to drilldown</a>' : '<br>Click to drilldown';
                                } else {
                                    drillDown = '';
                                }
                                if (elem.valuelabel !== 'undefined' && elem.valuelabel != undefined && elem.valuelabel !== '') {
                                    value = 'elem.valuelabel';
                                } else {
                                    value = 'elem.value';
                                }
                                eval('tip.innerHTML = "<b>" + elem.label + "</b>: " + ' + value + ' + drillDown');
                            }
                        }
                    });
                    //load JSON data.
                    gaugeChart.loadJSON(data);

                    var list = SUGAR.charts.generateLegend(gaugeChart, chartId);

                    //save canvas to image for pdf consumption
                    $jit.util.saveImageTest(chartId, jsonFilename, chartConfig['imageExportType']);

                    SUGAR.charts.trackWindowResize(gaugeChart, chartId, data);
                    that.chartObject = gaugeChart;
                }
                SUGAR.charts.callback(callback);
            });
            break;
    }
}

function swapChart(chartId, jsonFilename, css, chartConfig) {
    $('#d3_' + chartId).empty();
    var chart = new loadSugarChart(chartId, jsonFilename, css, chartConfig);
    return chart;
}

/**
 * As you touch the code above, migrate the code to use the pattern below.
 */
(function($) {

    if (typeof SUGAR == 'undefined' || !SUGAR) {
        SUGAR = {};
    }
    SUGAR.charts = {

        chart: null,
        json: [],

        /**
         * Execute callback function if specified
         *
         * @param callback function
         */
        callback: function(callback) {
            if (callback) {
                // if the call back is fired, include the chart as the only param
                callback(this.chart);
            }
        },

        setChartObject: function(d3) {
            this.chart = d3;
        },

        setChartData: function(data) {
            this.json = data;
        },

        renderChart: function(id) {
            $('#d3_' + id).empty();
            d3.select('#d3_' + id)
                .append('svg')
                .datum(this.json)
                .transition().duration(500)
                .call(this.chart);
        },

        /**
         * Handle the Legend Generation
         *
         * @param chart
         * @param chartId
         * @return {*}
         */
        generateLegend: function(chart, chartId) {
            var list = $jit.id('legend' + chartId);
            var legend = chart.getLegend();
            var table, i;
            if (typeof legend['wmlegend'] != 'undefined' && legend['wmlegend']['name'].length > 0) {
                table = '<div class="col">';
            } else {
                table = '<div class="row">';
            }
            for (i = 0; i < legend['name'].length; i++) {
                table += '<div class="legendGroup">';
                table += '<div class="query-color" style="background-color:' + legend['color'][i] + '"></div>';
                table += '<div class="label">';
                table += legend['name'][i];
                table += '</div>';
                table += '</div>';
            }

            table += '</div>';


            if (typeof legend['wmlegend'] != 'undefined' && legend['wmlegend']['name'].length > 0) {

                table += '<div class="col2">';
                for (i = 0; i < legend['wmlegend']['name'].length; i++) {
                    table += '<div class="legendGroup">';
                    table += '<div class="waterMark  ' + legend['wmlegend']['type'][i] + '" style="background-color:' + legend['wmlegend']['color'][i] + '"></div>';
                    table += '<div class="label">' + legend['wmlegend']['name'][i] + '</div>';
                    table += '</div>';
                }
                table += '</div>';

            }

            list.innerHTML = table;

            //adjust legend width to chart width
            jQuery('#legend' + chartId).ready(function() {
                var chartWidth = jQuery('#' + chartId).width();
                var sel;
                chartWidth = chartWidth - 20;
                $('#legend' + chartId).width(chartWidth);
                var legendGroupWidth = [];
                if (typeof legend['wmlegend'] != 'undefined' && legend['wmlegend']['name'].length > 0) {
                    sel = '.col .legendGroup';
                } else {
                    sel = '.row .legendGroup';
                }
                $(sel).each(function(index) {
                    legendGroupWidth[index] = $(this).width();
                });
                var largest = Math.max.apply(Math, legendGroupWidth);
                $(sel).width(largest + 2);
            });

            return list;
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

        translateString: function(appString, module) {
            if (SUGAR.language) {
                if (module) {
                    return SUGAR.language.get(module, appString);
                } else {
                    return SUGAR.language.get('app_strings', appString);
                }
            } else if (SUGAR.App) {
                if (module) {
                    return SUGAR.App.lang.getAppString(appString, module);
                } else {
                    return SUGAR.App.lang.getAppString(appString);
                }
            } else {
                return appString;
            }
        },

        translateDataToD3: function(json, params, chartConfig) {
            var data = [],
                strUndefined = SUGAR.charts.translateString('LBL_CHART_UNDEFINED');

            if (json.values.filter(function(d) { return d.values.length; }).length) {

                switch (chartConfig['chartType']) {

                    case 'barChart':
                        data = (chartConfig.barType === 'stacked') ?
                            json.label.map(function(d, i) {
                                return {
                                    'key': (d !== '') ? d : strUndefined,
                                    'type': 'bar',
                                    'values': json.values.map(function(e, j) {
                                        return { 'series': i, 'x': j + 1, 'y': (parseInt(e.values[i], 10) || 0), y0: 0};
                                    })
                                };
                            }) :
                            json.values.map(function(d, i) {
                                return {
                                    'key': (d.label[0] !== '') ? d.label[0] : strUndefined,
                                    'type': 'bar',
                                    'values': json.values.map(function(e, j) {
                                        return { 'series': i, 'x': j + 1, 'y': (i === j ? parseInt(e.values[0], 10) : 0), y0: 0};
                                    })
                                };
                            });
                        break;

                    case 'pieChart':
                        data = json.values.map(function(d, i) {
                            return {
                                'key': (d.label[0] !== '') ? d.label[0] : strUndefined,
                                'value': parseInt(d.values[0], 10)
                            };
                        });
                        break;

                    case 'funnelChart':
                        data = json.values.reverse().map(function(d, i) {
                            return {
                                'key': (d.label[0] !== '') ? d.label[0] : strUndefined,
                                'values': [{ 'series': i, 'x': 0, 'y': (parseInt(d.values[0], 10) || 0), y0: 0 }]
                            };
                        });
                        break;

                    case 'lineChart':
                        data = json.values.map(function(d, i) {
                            return {
                                'key': (d.label !== '') ? d.label : strUndefined,
                                'values': d.values.map(function(e, j) {
                                    return [j, parseInt(e, 10)];
                                })
                            };
                        });
                        break;
                }
            }

            return {
                'properties': {
                    'title': json.properties[0].title,
                    // bar group data (x-axis)
                    'labels': (!json.values.filter(function(d) { return d.values.length; }).length) ? [] :
                        json.values.map(function(d, i) {
                        return {
                            'group': i + 1,
                            'l': (d.label !== '') ? d.label : strUndefined
                        };
                    }),
                    'values': (!json.values.filter(function(d) { return d.values.length; }).length) ? [] :
                        json.values.map(function(d, i) {
                        return {
                            'group': i + 1,
                            't': d.values.reduce(function(p, c, i, a) {
                                return parseInt(p, 10) + parseInt(c, 10);
                            })
                        };
                    })
                },
                // series data
                'data': data
            };
        },

        translateParetoDataToD3: function(json, params) {
            return {
                'properties': {
                    'title': json.properties[0].title,
                    'quota': parseInt(json.values[0].goalmarkervalue[0], 10),
                    // bar group data (x-axis)
                    'groupData': (!json.values.filter(function(d) { return d.values.length; }).length) ? [] :
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
                'data': (!json.values.filter(function(d) { return d.values.length; }).length) ? [] :
                    json.label.map(function(d, i) {
                        return {
                            'key': d,
                            'type': 'bar',
                            'series': i,
                            'values': json.values.map(function(e, j) {
                                return { 'series': i, 'x': j + 1, 'y': parseInt(e.values[i], 10), y0: 0};
                            }),
                            'valuesOrig': json.values.map(function(e, j) {
                                return { 'series': i, 'x': j + 1, 'y': parseInt(e.values[i], 10), y0: 0};
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
                                            return {'series': i, 'x': j + 1, 'y': parseInt(e.goalmarkervalue[i + 1], 10)};
                                        }),
                                        'valuesOrig': json.values.map(function(e, j) {
                                            return {'series': i, 'x': j + 1, 'y': parseInt(e.goalmarkervalue[i + 1], 10)};
                                        })
                                    };
                                })
                        )
            };
        },
        /**
         * Is data returned from the server empty?
         *
         * @param data
         * @return {Boolean}
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
         * @param chart
         * @param chartId
         * @param json
         */
        trackWindowResize: function(chart, chartId, json) {
            var timeout,
                delay = 500,
                origWindowWidth = document.documentElement.scrollWidth;

            // refresh graph on window resize
            $(window).resize(function() {
                if (timeout) {
                    clearTimeout(timeout);
                }

                timeout = setTimeout(function() {
                    var newWindowWidth = document.documentElement.scrollWidth;

                    // if window width has changed during resize
                    if (newWindowWidth !== origWindowWidth) {
                        // measure container width
                        chart.update();
                        origWindowWidth = newWindowWidth;
                    }
                }, delay);
            });
        },

        /**
         * Update chart with new data from server
         *
         * @param chart
         * @param url
         * @param params
         * @param callback
         */
        update: function(chart, url, params, callback) {
            var self = this;
            params = params ? params : {};
            self.chart = chart;
            this.get(url, params, function(data) {
                if (self.isDataEmpty(data)) {
                    self.chart.busy = false;
                    self.chart.updateJSON(data);
                    self.callback(callback);
                }
            });
        },

        saveImageFile: function(id, jsonfilename, imageExt, saveTo, complete) {
            var self = this;
            var d3ChartId = '#d3_' + id + '_print' || 'd3_c3090c86-2b12-a65e-967f-51b642ac6165_print';
            var canvasChartId = 'canvas_' + id || 'canvas_c3090c86-2b12-a65e-967f-51b642ac6165';
            var svgChartId = 'svg_' + id || 'canvas_c3090c86-2b12-a65e-967f-51b642ac6165';

            var completeCallback = complete || function() {
                self.renderChart(id);
            };

            d3.select(d3ChartId + ' svg').remove();

            d3.select(d3ChartId)
                .append('svg')
                .attr('id', svgChartId)
                .datum(this.json)
                .call(this.chart);

            d3.select(d3ChartId).selectAll('.nv-axis line')
              .style('stroke', '#DDD')
              .style('stroke-width', 1)
              .style('stroke-opacity', 1);

            var parts = jsonfilename.split('/'),
                filename = parts[parts.length - 1].replace('.js', '.' + imageExt),
                oCanvas = document.getElementById(canvasChartId),
                d3Container = document.getElementById(svgChartId),
                serializer = new XMLSerializer(),
                saveToUrl = saveTo || 'index.php?action=DynamicAction&DynamicAction=saveImage&module=Charts&to_pdf=1';

            if (!oCanvas) {
                return;
            }

            $.ajax({
                url: 'styleguide/assets/css/nvd3_print.css',
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

                                $.post(saveToUrl, {imageStr: uri, filename: filename});

                                var ctx = oCanvas.getContext('2d');
                                ctx.clearRect(0, 0, 1440, 960);

                                completeCallback();
                            }
                        };

                    setTimeout(function() {
                        var svg = serializer.serializeToString(d3Container),
                            svgAttr = ' xmlns:xlink="http://www.w3.org/1999/xlink" width="720" height="480" viewBox="0 0 1440 960">',
                            cssCdata = '<style type="text/css"><![CDATA[' + css.trim() + ']]></style>',
                            d3Chart = svg.replace(/><g class="nvd3/, (svgAttr + cssCdata + '<g class="nvd3'));

                        canvg(canvasChartId, d3Chart, canvgOptions);
                    }, 1000);
                }
            });
        }
    };
})(jQuery);
