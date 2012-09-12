({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.guid = _.uniqueId("countrychart");
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

        App.api.call('GET', '../rest/v10/CustomReport/SalesByCountry', null, {success: function(o) {
            var results = {};
            var values = [];
            for (i = 0; i < o.length; i++) {
                var country = o[i]['country'];
                if("USA" == country) country = "United States of America";
                results[country] = parseInt(o[i]['amount']);
                values.push(parseInt(o[i]['amount']));
            }

            var color = d3.scale.linear().domain([0, _.max(values)]).range(["gray", "blue"]);
            var xy = d3.geo.equirectangular().scale($('#'+self.guid).width()).translate([$('#'+self.guid).width()/2, 150]);
            var svg = d3.select("#"+self.guid).append("svg").attr("style", "height: 250px;");
            var path = d3.geo.path().projection(xy);
            d3.json("../clients/summer/views/countrychart/world-countries.json", function(collection) {
                var g = svg.selectAll("path")
                  .data(collection.features)
                .enter().append("g");
                g.append("path")
                  .attr("d", function(d) { return path(d); })
                  .style("fill", function(d) {
                    return color(results[d.properties.name] || 0);
                  }).attr("title", function(d) { return d.properties.name; });
            });
        }});
    }
})
