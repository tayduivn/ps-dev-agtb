/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * View that displays a chart
 * @class View.Views.ForecastsChartView
 * @alias SUGAR.App.layout.ForecastsChartView
 * @extends View.View
 */
({
    values: new Backbone.Model(),
    chart: null,
    chartRendered: false,
    chartDataSet: [],
    chartGroupByOptions: [],
    defaultDataset: '',
    defaultGroupBy: '',
    chartTitle: '',
    timeperiod_label: '',
    stopRender: false,

    /**
     * events on the view to watch for
     */
    events : {
        'click #forecastsChartDisplayOptions div.datasetOptions label.radio' : 'changeDisplayOptions',
        'click #forecastsChartDisplayOptions div.groupByOptions label.radio' : 'changeGroupByOptions'
    },

    initialize : function(options) {
        app.view.View.prototype.initialize.call(this, options);

        // clear out the values if the object is re-inited.
        this.values.clear({silent: true});
    },

    /**
     * event handler to update which dataset is used.
     */
    changeDisplayOptions : function(evt) {
        evt.preventDefault();
        this.handleRenderOptions({dataset: this.handleOptionChange(evt)})
    },

    /**
     * Handle any group by changes
     */
    changeGroupByOptions: function(evt) {
        evt.preventDefault();
        this.handleRenderOptions({group_by:_.first(this.handleOptionChange(evt))});
    },

    /**
     * Handle the click event for the optins menu
     *
     * @param evt
     * @return {Array}
     */
    handleOptionChange: function(evt) {
        el = $(evt.currentTarget);
        // get the parent
        pel = el.parents('div:first');

        // un-check the one that is currently checked
        pel.find('label.checked').removeClass('checked');

        // check the one that was clicked
        el.addClass('checked');

        // return the dataset from the one that was clicked
        return [el.attr('data-set')];
    },

    /**
     * find all the checkedOptions in a give option class
     *
     * @param {string} divClass
     * @return {Array}
     */
    getCheckedOptions : function(divClass) {
        // find the checked options
        var chkOptions = this.$el.find("div." + divClass + " label.checked");

        // parse the array to get the data-set attributed
        var options = [];
        _.each(chkOptions, function(o) {
            options.push($(o).attr('data-set'));
        });

        // return the found options
        return options;
    },

    /**
     * Override the _rerderHtml function
     *
     * @protected
     */
    _renderHtml: function(ctx, options) {
        //this.chartTitle = app.lang.get("LBL_CHART_FORECAST_FOR", "Forecasts") + ' ' + app.defaultSelections.timeperiod_id.label;
        this.timeperiod_label = app.defaultSelections.timeperiod_id.label;

        this.chartDataSet = app.utils.getAppConfigDatasets('forecasts_options_dataset', 'show_worksheet_');
        this.chartGroupByOptions = app.metadata.getStrings('app_list_strings').forecasts_chart_options_group || [];
        this.defaultDataset = app.defaultSelections.dataset;
        this.defaultGroupBy = app.defaultSelections.group_by;

        // make sure that the default data set is actually shown, if it's not then we need
        // to set it to the first available option from the allowed dataset.
        if(_.isUndefined(this.chartDataSet[this.defaultDataset])) {
            this.defaultDataset = _.first(_.keys(this.chartDataSet));
        }

        app.view.View.prototype._renderHtml.call(this, ctx, options);

        var values = {
            user_id: app.user.get('id'),
            display_manager : app.user.get('isManager'),
            timeperiod_id : app.defaultSelections.timeperiod_id.id,
            group_by : _.first(this.getCheckedOptions('groupByOptions')),
            dataset : this.getCheckedOptions('datasetOptions'),
            ranges: app.defaultSelections.ranges
        };

        this.handleRenderOptions(values);
    },

    _render : function() {
        app.view.View.prototype._render.call(this);

        this.toggleRepOptionsVisibility();
    },

    toggleRepOptionsVisibility : function() {
        if(this.values.get('display_manager') === true) {
            this.$el.find('div.groupByOptions').hide();
        } else {
            this.$el.find('div.groupByOptions').show();
        }
    },

    /**
     * Clean up any left over bound data to our context
     */
    unbindData : function() {
        if(this.context.worksheetmanager) this.context.worksheetmanager.off(null, null, this);
        if(this.context) this.context.off(null, null, this);
        if(this.values) this.values.off(null, null, this);
        app.view.View.prototype.unbindData.call(this);
    },

    /**
     * Listen to changes in values in the context
     */
    bindDataChange:function () {
        //This is fired when anything in the worksheets is saved.  We want to wait until this happens
        //before we go and grab new chart data.
        this.context.on("forecasts:committed:saved", function(){
            this.renderChart();
        }, this);

        this.context.on("forecasts:worksheet:saved", function(totalSaved, worksheet, isDraft) {
            // we only want this to run if the totalSaved was greater than zero and we are saving the draft version
            if(totalSaved > 0 && isDraft == true) {
                this.renderChart();
            }
        }, this);

        this.context.on('change:selectedUser', function (context, user) {
            if(!_.isEmpty(this.chart)) {
                this.handleRenderOptions({user_id: user.id, display_manager : (user.showOpps === false && user.isManager === true)});
                this.toggleRepOptionsVisibility();
            }
        }, this);
        this.context.on('change:selectedTimePeriod', function (context, timePeriod) {
            if(!_.isEmpty(this.chart)) {
                this.timeperiod_label = timePeriod.label;
                this.handleRenderOptions({timeperiod_id: timePeriod.id});
            }
        }, this);
        this.context.on('change:selectedGroupBy', function (context, groupBy) {
            if(!_.isEmpty(this.chart)) {
                this.handleRenderOptions({group_by: groupBy});
            }
        }, this);
        this.context.on('change:selectedRanges', function(context, value) {
            if(!_.isEmpty(this.chart)) {
                this.handleRenderOptions({ranges: value});
            }
        }, this);
        this.context.on('forecasts:commitButtons:sidebarHidden', function(value){
            // set the value of the hiddenSidecar to we can stop the render if the sidebar is hidden
            this.stopRender = value;
            // if the sidebar is not hidden
            if(value == false){
                // we need to force the render to happen again
                this.renderChart();
            }
        }, this);
        // watch for the change event to fire.  if it fires make sure something actually changed in the array
        this.values.on('change', function(value) {
            if(!_.isEmpty(value.changed)) {
                this.renderChart();
            }
        }, this);
    },

    /**
     * Handle putting the options into the values array that is used to keep track of what changes
     * so we only render when something changes.
     * @param options
     */
    handleRenderOptions:function (options) {
        this.values.set(options);
    },

    /**
     * Initialize or update the chart
     */
    renderChart:function () {
        this._initializeChart();
    },

    /**
     * Render the chart for the first time
     *
     * @return {Object}
     * @private
     */
    _initializeChart:function () {

        if(this.stopRender) {
            return {};
        }

        var chart,
            self = this,
            chartId = "db620e51-8350-c596-06d1-4f866bfcfd5b",
            css = {
                "gridLineColor":"#cccccc",
                "font-family":"Arial",
                "color":"#000000"
            },
            chartConfig = {
                "orientation":"vertical",
                "barType": this.values.get('display_manager') ? "grouped" : "stacked",
                "tip":"name",
                "chartType":"d3-barChart",
                "imageExportType":"png",
                "showNodeLabels":false,
                "showAggregates":false,
                "saveImageTo":"",
                "dataPointSize":"5"
            };

        var oldChart = $("#" + chartId );
        if(!_.isEmpty(oldChart)) {
            d3.select('#' + chartId + ' svg').remove();
        }

        SUGAR.charts = $.extend(SUGAR.charts,
            {
              get : function(url, params, success)
              {
                  var data = {
                      r: new Date().getTime()
                  };
                  data = $.extend(data, params);

                  url = app.api.buildURL(self.buildChartUrl(params), '', '', data);

                  app.api.call('read', url, data, {success : success});
              },
                translateDataToD3 : function( json, params )
                {
                    return {
                        'properties':{
                            'title': json.properties[0].title
                            , 'quota': parseInt(json.values[0].goalmarkervalue[0],10)
                            // bar group data (x-axis)
                            , 'groupData': (!json.values.filter(function(d) { return d.values.length }).length) ? [] :
                                json.values.map( function(d,i){
                                    return {
                                        'group': i
                                        , 'l': json.values[i].label
                                        , 't': json.values[i].values.reduce( function(p, c, i, a){
                                            return parseInt(p,10) + parseInt(c,10);
                                        })
                                    }
                                })
                        }
                        // series data
                        , 'data': (!json.values.filter(function(d) { return d.values.length }).length) ? [] :
                            json.label.map( function(d,i){
                                return {
                                    'key': d
                                    , 'type': 'bar'
                                    , 'series': i
                                    , 'values': json.values.map( function(e,j){
                                        return { 'series': i, 'x': j+1, 'y': parseInt(e.values[i],10), y0: 0 };
                                    })
                                    , 'valuesOrig': json.values.map( function(e,j){
                                        return { 'series': i, 'x': j+1, 'y': parseInt(e.values[i],10), y0: 0 };
                                    })
                                }
                        }).concat(
                            json.properties[0].goal_marker_label.filter( function(d,i){
                                return d !== 'Quota';
                            }).map( function(d,i){
                                    return {
                                        'key': d
                                        , 'type': 'line'
                                        , 'series': i
                                        , 'values': json.values.map( function(e,j){
                                            return { 'series': i, 'x': j+1, 'y': parseInt(e.goalmarkervalue[i+1],10) };
                                        })
                                        , 'valuesOrig': json.values.map( function(e,j){
                                            return { 'series': i, 'x': j+1, 'y': parseInt(e.goalmarkervalue[i+1],10) };
                                        })
                                    }
                                })
                        )
                    };
                }
            }
        );

        if(this.values.get('display_manager') === true) {
            this.values.set({ranges: 'include'}, {silent: true});
        }

        // update the chart title
        var hb = Handlebars.compile("{{str_format key module args}}");
        var text = hb({'key' : "LBL_CHART_FORECAST_FOR", 'module' : 'Forecasts', 'args' : this.timeperiod_label});
        this.$el.find('h4').html(text);

        var params = this.values.toJSON() || {};
        params.contentEl = 'chart';
        params.minColumnWidth = 120;
        params.chartId = chartId;

        chart = new loadSugarChart(chartId, this.buildChartUrl(params), css, chartConfig, params, _.bind(function(chart){
            this.chart = chart;
        }, this));
        this.chartRendered = true;
    },

    /**
     * Accepts params object and builds the proper endpoint url for charts
     * @param params {Object} contains a lot of chart options and settings
     * @return {String} has the proper structure for the chart url
     */
    buildChartUrl: function(params) {
        var baseUrl = params.display_manager ? 'ForecastManagerWorksheets' : 'ForecastWorksheets';
        return baseUrl + '/chart/' + params.timeperiod_id + '/' + params.user_id;
    }
})
