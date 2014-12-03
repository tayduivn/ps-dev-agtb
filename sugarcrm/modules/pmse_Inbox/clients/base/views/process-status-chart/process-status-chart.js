({
    plugins: ['Dashlet', 'Chart'],
    //className: 'process-status-chart',
    processCollection: null,
    currentValue: 'all',
    chartCollection: null,
    hasData: false,
    total: 0,

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.chart = nv.models.multiBarChart()
            .showTitle(false)
            .showControls(true)
            .showValues(false)
            .stacked(true)
            .tooltipContent(function(key, x, y, e, graph) {
                return '<p><b>' + parseInt(y, 10) + '</b></p>';
            })
            .tooltips(true);
    },

    hasChartData: function () {
        return this.hasData;
    },

    /**
     * Generic method to render chart with check for visibility and data.
     * Called by _renderHtml and loadData.
     */
    renderChart: function() {
        if (!this.isChartReady()) {
            return;
        }

        d3.select(this.el).select('svg#' + this.cid)
            .datum(this.chartCollection)
            .transition().duration(500)
            .call(this.chart);

        this.chart_loaded = _.isFunction(this.chart.update);
        this.displayNoData(!this.chart_loaded);
    },

    /**
     * @inheritDoc
     */
    loadData: function(options) {
        var self = this,
            url;
        if (this.meta.config) {
            return;
        }
        if (!this.currentValue) {
            return;
        }
        url = app.api.buildURL('pmse_Inbox/processStatusChart/' + this.currentValue);
        this.hasData = false;
        app.api.call('GET', url, null, {
            success: function(data) {
                self.evaluateResponse(data);
                self.renderChart();
            },
            complete: options ? options.complete : null
        });
    },

    evaluateResponse: function(response) {
        this.hasData = true;
        this.chartCollection = response;
    }
})