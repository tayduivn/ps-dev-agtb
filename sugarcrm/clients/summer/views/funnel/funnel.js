({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.collection = app.data.createBeanCollection(this.model.module);
        this.collection.fetch({limit: 999999});
        this.guid = _.uniqueId("funnel");
    },

    _render: function() {
        var self = this;

        this.$el.show();

        var layoutData = {guid: this.guid, title: this.options['title']};
        app.view.View.prototype._render.call(this);

        // Once the data is fetched, process it, then render it.
        this.collection.on("reset", function() {
            var day_ms = 1000*60*60*24;
            var today = new Date();
            today.setUTCHours(0,0,0,0);
            var d1 = new Date(today.getTime() + 31*day_ms);
            var data, sum;
            if(self.collection) {
                var data = self.collection.filter(function(model) {
                    // Filter for 30 days from now.
                    var d2 = new Date(model.get("date_closed") || "1970-01-01");
                    return (d2-d1)/day_ms <= 30;
                });
                sum = _.reduce(data, function(memo, model) {
                    return memo + parseInt(model.get('amount_usdollar'));
                }, 0);
                data = _.groupBy(data, function(m) {
                    return m.get("sales_stage");
                });
            }

            var stages = ["Prospecting", "Qualification", "Closed Lost", "Closed Won"];
            var scale = 1000;

            // Massage the values to what we want.
            // TODO: Make this more efficient.
            var root = {
                properties: {
                    scale: scale,
                    title: "Pipeline",
                    units: "$",
                    total: parseInt(sum/scale)
                },
                data: []
            };

            var cumulative = 0;

            _.each(stages, function(stage, i) {
                var subtotal = 0;
                if(data && data[stage]) {
                    subtotal = _.reduce(data[stage], function(memo, model) {
                        return memo + parseInt(model.get('amount_usdollar'));
                    }, 0)/scale;
                }
                root.data.push({
                    bar: true,
                    key: stage,
                    values: [{
                        series: i,
                        x: 0,
                        y: subtotal,
                        y0: cumulative
                    }]
                });
                cumulative += subtotal;
            });

            console.log(root);
            nv.addGraph(function() {
                var chart = nv.models.funnelChart();

                // chart.xAxis
                //     .tickFormat(d3.format(',f'));

                chart.yAxis
                    .tickFormat(d3.format(',.1f'));

                chart.showTitle(false);

                d3.select('#'+self.guid+' svg')
                    .datum(root)
                  .transition().duration(500).call(chart);

                nv.utils.windowResize(chart.update);

                return chart;
            });

        });
    }
})
