/**
 * View that displays a chart
 * @class View.Views.ChartView
 * @alias SUGAR.App.layout.ChartView
 * @extends View.View
 */
({

    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
    },

    /**
     * Render the chart
     */
    render:function () {
        var forecast,
            chartId = "db620e51-8350-c596-06d1-4f866bfcfd5b",
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
        forecast = new loadSugarChart(chartId, 'rest/v10/Forecasts/chart', css, chartConfig);
    }

})