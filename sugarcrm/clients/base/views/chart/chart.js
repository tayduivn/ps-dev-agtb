/**
 * View that displays a chart
 * @class View.Views.ChartView
 * @alias SUGAR.App.layout.ChartView
 * @extends View.View
 */
({

    currentUserId: null,
    url: 'rest/v10/Forecasts/chart',

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this.currentUserId = app.user.get('id');
    },

    /**
     * Listen to changes in selectedUser and selectedTimePeriod
     */
    bindDataChange: function() {
        var self = this,
            chart = null,
            currentTimePeriod = null;

        this.context.on('change:selectedUser', function(context, user) {
            self.currentUserId = user.id;
            self.renderChart(chart, currentTimePeriod);
        });
        this.context.on('change:selectedTimePeriod', function(context, timePeriod) {
            currentTimePeriod = timePeriod.id;
            self.renderChart(chart, currentTimePeriod);
        });
    },

    /**
     * Initialize or update the chart
     */
    renderChart: function(chart, currentTimePeriod) {
        var loadingMessage;

        if (currentTimePeriod) {
            loadingMessage= SUGAR.App.alert.show('loading', {level: 'process', messages: 'Loading...'});
            if (chart === null) {
                chart = this._initializeChart(currentTimePeriod, function() {
                    loadingMessage.close();
                });
            } else {
                SUGAR.charts.update(chart, this.url, {
                    user: this.currentUserId,
                    tp: currentTimePeriod
                }, function() {
                    loadingMessage.close();
                });
            }
        }
    },

    /**
     * Render the chart for the first time
     */
    _initializeChart: function (currentTimePeriod, callback) {
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
        chart = new loadSugarChart(chartId, this.url, css, chartConfig, {
            user: this.currentUserId,
            tp: currentTimePeriod
        }, callback);
        return chart.chartObject;
    }

})