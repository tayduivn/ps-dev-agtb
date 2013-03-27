({
    results: {},
    chart: {},
    plugins: ['Dashlet'],

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
    },

    _renderHtml: function() {
        var chart, svg;

        app.view.View.prototype._renderHtml.call(this);

        if(!_.isEmpty(this.results)) {
            chart = nv.models.funnelChart()
                .showTitle(true)
                .tooltips(true)
                .colorData('default')
                .colorFill('default')
                .tooltip(function(key, x, y, e, graph) {
                    return '<p>Stage: <b>' + key + '</b></p>' +
                        '<p>Amount: <b>' + app.currency.formatAmountLocale(y, app.currency.getBaseCurrencyId()) + '</b></p>' +
                        '<p>Percent: <b>' + x + '%</b></p>'
                });

            chart.yAxis
                .tickFormat(d3.format(',.1f'));

            d3.select('svg#' + this.cid)
                .datum(this.results)
                .transition().duration(500).call(chart);

            nv.utils.windowResize(chart.update);

            this.chart = chart;
        }
    },
    loadData: function(options) {
        var url = app.api.buildURL('Opportunities/chart/pipeline');
        app.api.call('GET', url, null, {
            success: _.bind(function(o) {
                console.log(o);
                this.results = {};
                this.results = o;
                if(!this.disposed) this.render();
            }, this),
            complete: options ? options.complete : null
        });
    }
})
