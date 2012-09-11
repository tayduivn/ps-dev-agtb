({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.guid = _.uniqueId("leaderboard");

        ikea = this;
    },

    _render: function() {
        var self = this;

        this.$el.show();

        var layoutData = {guid: this.guid, title: this.options['title']};

        app.view.View.prototype._render.call(this);

        App.api.call('GET', '../rest/v10/CustomReport/OpportunityLeaderboard', null, {success: function(o) {
            var results = [{
                key: "Opportunity Leaderboard",
                values: []
            }];
            for (i = 0; i < o.length; i++) {
                results[0].values.push({
                    label: o[i]['user_name'],
                    value: parseInt(o[i]['amount'])
                });
            }

            $("#" + self.guid + " svg").css("width", $("#" + self.guid).width());
            $("#" + self.guid + " svg").css("min-height", "300px");
            nv.addGraph(function() {
                var chart = nv.models.pieChart()
                  .x(function(d) { console.log(d); return d.label })
                  .y(function(d) { return d.value })
                  .showLabels(true);

                d3.select("#" + self.guid + " svg")
                  .datum(results)
                  .transition().duration(1200)
                  .call(chart);

                return chart;
            });
        }});
    }
})
