({
    results: {},
    chart: {},
    plugins: ['Dashlet'],

    events: {
        'click button.btn': 'handleTypeButtonClick'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        // since we need the timeperiods from 'Forecasts' set the models module to 'Forecasts'
        this.model.module = 'Forecasts';

        // set the default button state
        this.model.set({'type': 'self'}, {silent: true});

        // get the current timeperiod
        app.api.call('GET', app.api.buildURL('TimePeriods/current'), null, {
            success: _.bind(function(o) {
                this.model.set({'selectedTimePeriod': o.id}, {silent: true});
            }, this),
            complete: options ? options.complete : null
        });
    },

    handleTypeButtonClick: function(e) {
        var elm = $(e.currentTarget),
            displayType = elm.data('type');
        if(this.model.get('type') != displayType) {
            this.model.set({'type': displayType});
        }
    },

    bindDataChange: function() {
        this.model.on('change', function(model) {
            // reload the chart
            this.loadData({});
        }, this);
    },

    renderChart: function() {
        var chart, svg;
        // clear out the current chart before a re-render
        this.$el.find('.nv-chart').html('<svg id="view51"></svg>');
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
    },

    loadData: function(options) {

        var url_base = 'Opportunities/chart/pipeline';
        if(this.model.has('selectedTimePeriod')) {
            url_base += '/' + this.model.get('selectedTimePeriod');
            if(this.model.has('type')) {
                url_base += '/' + this.model.get('type');
            }
        }
        var url = app.api.buildURL(url_base);
        app.api.call('GET', url, null, {
            success: _.bind(function(o) {
                this.results = {};
                this.results = o;
                this.renderChart();
            }, this),
            complete: options ? options.complete : null
        });
    }
})
