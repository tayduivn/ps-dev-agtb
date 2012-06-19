/**
 * View that displays a chart
 * @class View.Views.ChartView
 * @alias SUGAR.App.layout.ChartView
 * @extends View.View
 */
({

    chart: null,
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

    bindDataChange: function() {
        var self = this;
        this.context.on('change:selectedUser', function(context, user) {
            self.currentUserId = user.id;
            updateChart(self.url, self.chart.chartObject, {
                user: self.currentUserId
            });
        });
        this.context.on('change:selectedTimePeriod', function(context, timePeriod) {
            updateChart(self.url, self.chart.chartObject, {
                user: self.currentUserId
            });
        });
    },

    /**
     * Render the chart
     */
    render:function () {
        var chartId = "db620e51-8350-c596-06d1-4f866bfcfd5b",
            css = {
                "gridLineColor":"#cccccc",
                "font-family":"Arial",
                "color":"#000000"
            },
            chartConfig = {
                "orientation":"vertical",
                "barType":"stacked",
                "tip":"label",
                "chartType":"barChart",
                "imageExportType":"png",
                "showNodeLabels":false,
                "showAggregates":false,
                "saveImageTo":"index.php?action=DynamicAction&DynamicAction=saveImage&module=Charts&to_pdf=1"
            };
        app.view.View.prototype.render.call(this);
        this.chart = new loadSugarChart(chartId, this.url, css, chartConfig, {
            user: this.currentUserId
        });
    }

})