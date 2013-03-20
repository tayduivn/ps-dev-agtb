({
    results: {},
    chart: {},
    reporteesEndpoint:'',
    currentTreeUrl:'',
    currentRootId:'',
    plugins: ['Dashlet'],

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
    },

    _renderHtml: function() {
        var self = this,
            node, max, color, xy, svg, path, width, height;

        app.view.View.prototype._renderHtml.call(this);

        if (!_.isEmpty(this.results)) {
            //$("div#" + this.cid).append("test");

            var funnel_data = {
                'properties': {
                    'title': 'Pipeline total is $675k',
                    'total': 675,
                    'scale': 1000,
                    'units': '$'
                },
                'data': [
                    {
                        key: 'Negotiation/Review',
                        'bar': true,
                        'values': [
                            {series: 0, x: 0, y: 75,  y0: 0},
                        ]
                    },
                    {
                        'key': 'Proposal/PriceQuote',
                        'bar': true,
                        'values': [
                            {series: 1, x: 0, y: 110,  y0:  75},
                        ]
                    }
                ]
            };

             chart = nv.models.funnelChart()
                    .showTitle(true)
                    .tooltips(true)
                    //.colorData( 'graduated', {c1: '#e8e2ca', c2: '#3e6c0a', l: chartData.data.length} )
                    //.colorData( 'class' )
                    .colorData( 'default' )
//                    .colorFill( 'gradient' )
                    .colorFill( 'default' )
                    .tooltip( function(key, x, y, e, graph) {
                        return '<p>Stage: <b>' + key + '</b></p>' +
                            '<p>Amount: <b>$' +  parseInt(y) + 'K</b></p>' +
                            '<p>Percent: <b>' +  x + '%</b></p>'
                    });

            chart.yAxis
                .tickFormat(d3.format(',.1f'));

            d3.select('svg#'+this.cid)
                .datum(funnel_data)
                .transition().duration(500).call(chart);

            nv.utils.windowResize(chart.update);

            self.chart = chart;
        }
    },
    loadData: function(options) {
        var self = this,
            url = app.api.buildURL('Accounts/by_country');
        app.api.call('GET', url, null, {
            success: function(o) {
                self.results = {};
                self.results = o;
                if (!self.disposed) self.render();
            },
            complete: options ? options.complete : null
        });
    }
})
