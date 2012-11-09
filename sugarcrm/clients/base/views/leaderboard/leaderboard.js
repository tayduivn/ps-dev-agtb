({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.results = [];
        this.guid = _.uniqueId("leaderboard");
        this.loadData();
    },

    _render: function() {
        var self = this;
        $("#" + this.guid + " svg").css("width", $("#" + this.guid).width());
        $("#" + this.guid + " svg").css("min-height", "300px");
    },

    loadData: function() {
        var self = this,
            url = app.api.buildURL('CustomReport/OpportunityLeaderboard');
        app.api.call('GET', url, null, {success: function(o) {
            self.results = {
                properties: {
                    title: 'Opportunity Leaderboard'
                },
                data: []
            };
            for (i = 0; i < o.length; i++) {
                self.results.data.push({
                    key: o[i]['user_name'],
                    value: parseInt(o[i]['amount'], 10)
                });
            }

            app.view.View.prototype._render.call(self);
            nv.addGraph(function() {
                var chart = nv.models.pieChart()
                  .x(function(d) { return d.label; })
                  .y(function(d) { return d.value; });

                d3.select("#" + self.guid + " svg")
                  .datum(self.results)
                  .transition().duration(1200)
                  .call(chart);
                return chart;
            });
        }});
    }
})
