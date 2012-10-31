({
    results: {},

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.guid = _.uniqueId("countrychart");
    },

    render: function() {
        var self = this,
            node, max, color, xy, svg, path;

        app.view.View.prototype.render.call(this);

        if (!_.isEmpty(this.results)) {
            node = $('#' + this.guid);
            max = _.max(_(this.results).values());
            color = d3.scale.linear().domain([0, max]).range(["gray", "blue"]);
            xy = d3.geo.equirectangular().scale(node.width()).translate([node.width() / 2, 150]);
            svg = d3.select("#" + this.guid).append("svg").attr("style", "height: 250px;");
            path = d3.geo.path().projection(xy);

            d3.json("../clients/summer/views/countrychart/world-countries.json", function(collection) {
                var g = svg.selectAll("path")
                    .data(collection.features)
                    .enter().append("g");
                g.append("path")
                    .attr("d", function(d) {
                        return path(d);
                    })
                    .style("fill",function(d) {
                        return color(self.results[d.properties.name] || 0);
                    }).attr("title", function(d) {
                        return d.properties.name;
                    });
            });
        }
    },

    loadData: function() {
        var self = this;

        app.api.call('GET', '../rest/v10/CustomReport/SalesByCountry', null, {
            success: function(o) {
                var i;

                self.results = {};

                for (i = 0; i < o.length; i++) {
                    var country = o[i]['country'];
                    if (country == "USA") {
                        country = "United States of America";
                    }
                    self.results[country] = parseInt(o[i]['amount'], 10);
                }

                self.render();
            }
        });
    },

    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("change", this.loadData);
        }
    }
})
