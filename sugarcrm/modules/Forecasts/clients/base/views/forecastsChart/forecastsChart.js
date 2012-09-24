/**
 * View that displays a chart
 * @class View.Views.ForecastsChartView
 * @alias SUGAR.App.layout.ForecastsChartView
 * @extends View.View
 */
({
    values:{},
    url:'rest/v10/Forecasts/chart',

    chart: null,

    chartRendered: false,
    commitUpdate: false,

    chartTitle: '',
    timeperiod_label: '',

    /**
     * events on the view to watch for
     */
    events : {
        'click #forecastsChartDisplayOptions input[type=radio]' : 'changeDisplayOptions'
    },

    /**
     * event handler to update which dataset is used.
     */
    changeDisplayOptions : function()
    {
        this.handleRenderOptions({dataset: this.getCheckedDisplayOptions()})
    },

    /**
     * find all the checkedDisplayOptions
     *
     * @return {Array}
     */
    getCheckedDisplayOptions : function()
    {
        var chkOptions = this.$el.find("input[type=radio]:checked");

        var datasets = [];
        _.each(chkOptions, function(o) {
            datasets.push(o.value);
        });

        return datasets;
    },

    /**
     * Override the _rerderHtml function
     *
     * @protected
     */
    _renderHtml: function(ctx, options) {
        //this.chartTitle = app.lang.get("LBL_CHART_FORECAST_FOR", "Forecasts") + ' ' + app.defaultSelections.timeperiod_id.label;
        this.timeperiod_label = app.defaultSelections.timeperiod_id.label;

        app.view.View.prototype._renderHtml.call(this, ctx, options);

        var values = {
            user_id: app.user.get('id'),
            display_manager : app.user.get('isManager'),
            timeperiod_id : app.defaultSelections.timeperiod_id.id,
            group_by : app.defaultSelections.group_by.id,
            dataset : this.getCheckedDisplayOptions(),
            category : app.defaultSelections.category
        };

        this.handleRenderOptions(values);
    },

    /**
     * Listen to changes in values in the context
     */
    bindDataChange:function () {
        var self = this;
        this.context.forecasts.worksheetmanager.on('reset', function(){

            if(self.commitUpdate && self.chartRendered) {
                self.commitUpdate = false;
                self.renderChart();
            }
        }, this);
        this.context.forecasts.on("change:commitForecastFlag", function(context, flag) {
            if(flag) {
                self.commitUpdate = true;
            }
        }, this);
        this.context.forecasts.on('change:selectedUser', function (context, user) {
            self.handleRenderOptions({user_id: user.id, display_manager : (user.showOpps === false && user.isManager === true)});
        });
        this.context.forecasts.on('change:selectedTimePeriod', function (context, timePeriod) {
            self.timeperiod_label = timePeriod.label;
            self.handleRenderOptions({timeperiod_id: timePeriod.id});
        });
        this.context.forecasts.on('change:selectedGroupBy', function (context, groupBy) {
            self.handleRenderOptions({group_by: groupBy});
        });
        this.context.forecasts.on('change:selectedDataSet', function (context, dataset) {
            //self.handleRenderOptions({dataset: dataset});
        });
        this.context.forecasts.on('change:selectedCategory', function(context, value) {
            if (app.config.show_buckets == 0) {
                // TODO: this.
            } else {
                self.handleRenderOptions({category:_.first(value)});
            }
        });
    },

    handleRenderOptions:function (options) {
        var self = this;
        _.each(options, function (value, key) {
            self.values[key] = value;
        });

        self.renderChart();
    },

    /**
     * Initialize or update the chart
     */
    renderChart:function () {
        this.chart = this._initializeChart();
    },

    /**
     * Only update the json on the chart
     */
    updateChart: function() {
        var self = this;
        SUGAR.charts.update(self.chart, self.url, self.values, _.bind(function(chart){
            SUGAR.charts.generateLegend(chart, chart.config.injectInto)
            // update the chart title
            self.$el.find('h4').html(self.chartTitle);
        }, self));
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
                "barType": this.values.display_manager ? "grouped" : "stacked",
                "tip":"name",
                "chartType":"barChart",
                "imageExportType":"png",
                "showNodeLabels":false,
                "showAggregates":false,
                "saveImageTo":"",
                "dataPointSize":"5"
            };

        var oldChart = $("#" + chartId + "-canvaswidget");
        if(!_.isEmpty(oldChart)) {
            oldChart.remove();
        }

        SUGAR.charts = $.extend(SUGAR.charts,
            {
              get : function(url, params, success)
              {
                  var data = {
                      r: new Date().getTime()
                  };
                  data = $.extend(data, params);

                  url = app.api.buildURL('Forecasts', 'chart', '', data);

                  app.api.call('read', url, data, {success : success});
              }
            }
        );

        if(this.values.display_manager === true) {
            this.values.category = "Committed";
        }

        // update the chart title
        var hb = Handlebars.compile("{{str_format key module args}}");
        var text = hb({'key' : "LBL_CHART_FORECAST_FOR", 'module' : 'Forecasts', 'args' : this.timeperiod_label});
        this.$el.find('h4').html(text);

        chart = new loadSugarChart(chartId, this.url, css, chartConfig, this.values);
        this.chartRendered = true;
        return chart.chartObject;
    }
})