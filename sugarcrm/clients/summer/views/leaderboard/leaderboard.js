({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.guid = _.uniqueId("leaderboard");

        ikea = this;
    },

    _render: function() {
        var self = this;

        this.$el.show();

        app.view.View.prototype._render.call(this);

        var layoutData = {guid: this.guid, title: this.options['title']};

        if (typeof(this.options['urls']) != 'undefined') {
            layoutData['urls'] = this.options['urls'];
        }

        app.view.View.prototype._render.call(this);

        $('.chartSelector').val(this.options['url']);

        App.api.call('GET', '../rest/v10/CustomReport/OpportunityLeaderboard', null, {success: function(o) {
            var results = [];
            for (i = 0; i < o.length; i++) {
                results[results.length] = [o[i]['user_name'], parseInt(o[i]['amount'])];
            }

            var data = new google.visualization.DataTable();
            data.addColumn('string', 'User');
            data.addColumn('number', 'Amount');
            data.addRows(results);

            var options = {
                colorAxis: {
                    minValue: 0,
                    colors: ['gray', '#4D90FE']}
            };

            console.log(self.guid);
            var chart = new google.visualization.PieChart(document.getElementById(self.guid));
            chart.draw(data, options);
        }});
    },

    getData: function() {
        var url = this.options['url'];
        $.ajax({
            url: url,
            dataType: "json",
            success: this.render,
            context: this
        });
    }
})
