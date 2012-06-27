/**
 * View that displays a chart
 * @class View.Views.ChartView
 * @alias SUGAR.App.layout.ChartView
 * @extends View.View
 */
({

    hasChartOptions:false,
    hasFilterOptions:false,

    values:{},
    url:'rest/v10/Forecasts/chart',

    chart: null,

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this.handleRenderOptions({user_id: app.user.get('id')});
    },

    /**
     * Listen to changes in values in the context
     */
    bindDataChange:function () {
        var self = this;

        this.context.on('change:selectedUser', function (context, user) {
            self.handleRenderOptions({user_id: user.id});
        });
        this.context.on('change:selectedTimePeriod', function (context, timePeriod) {
            self.handleRenderOptions({timeperiod_id: timePeriod.id});
        });
        this.context.on('change:selectedGroupBy', function (context, groupBy) {
            self.handleRenderOptions({group_by: groupBy.id});
        });
        this.context.on('change:selectedDataSet', function (context, dataset) {
            self.handleRenderOptions({dataset: dataset.id});
        });
        this.context.on('change:selectedCategory', function(context, value) {
            self.handleRenderOptions({category: value.id});
        });

        this.context.on('change:renderedForecastFilter', function (context, value) {
            self.hasFilterOptions = true;
            self.handleRenderOptions(value);
        });

        this.context.on('change:renderedChartOptions', function (context, value) {
            self.hasChartOptions = true;
            self.handleRenderOptions(value);
        })
    },

    handleRenderOptions:function (options) {
        var self = this;
        _.each(options, function (value, key) {
            self.values[key] = value;
        });

        if (self.hasChartOptions && self.hasFilterOptions) {
            self.renderChart();
        }
    },

    /**
     * Initialize or update the chart
     */
    renderChart:function () {
        //if (this.chart === null) {
            this.chart = this._initializeChart();
        //} else {
        //    updateChart(this.url, this.chart, this.values);
        //}
    },

    /**
     * Render the chart for the first time
     *
     * @return {Object}
     * @private
     */
    _initializeChart:function () {
        var chart,
            chartId = "db620e51-8350-c596-06d1-4f866bfcfd5b",
            css = {
                "gridLineColor":"#cccccc",
                "font-family":"Arial",
                "color":"#000000"
            },
            chartConfig = {
                "orientation":"vertical",
                "barType":"stacked",
                "tip":"name",
                "chartType":"barChart",
                "imageExportType":"png",
                "showNodeLabels":false,
                "showAggregates":false,
                "saveImageTo":"",
                "dataPointSize":"5"
            };
        app.view.View.prototype.render.call(this);
        chart = new loadSugarChart(chartId, this.url, css, chartConfig, this.values);
        return chart.chartObject;
    }

})