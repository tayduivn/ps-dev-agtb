({
    results: {},
    plugins: ['Dashlet'],

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
    },

    _renderHtml: function() {
        var self = this,
            node, max, color, xy, svg, path, width, height;

        app.view.View.prototype._renderHtml.call(this);

        if (!_.isEmpty(this.results)) {
            node = this.$('svg');
            width = parseInt(node.width(), 10);
            height = parseInt(node.css('max-height'), 10);
            max = _.max(_(this.results).values());
            color = d3.scale.linear().domain([0, max]).range(["gray", "blue"]);
            xy = d3.geo.equirectangular()
                   .scale(height)
                   .translate([width / 2 - 0.5, height / 2]);
            svg = d3.select("svg#" + this.cid).append('g');
            path = d3.geo.path().projection(xy);

            d3.json(app.config.siteUrl + "/clients/base/views/countrychart/world-countries.json", function(collection) {
                var g = svg.selectAll("path")
                    .data(collection.features)
                    .enter().append("g");
                var bbox = {tl_x: 9999, tl_y: 9999, br_x: 0, br_y: 0};
                g.append("path")
                    .attr("d", function(d) {
                        return path(d);
                    }).style("fill",function(d) {
                        return color(self.results[d.properties.name] || 0);
                    }).attr("title", function(d) {
                        return d.properties.name;
                    });
                g.each(function(d, i) {
                    if(self.results[d.properties.name] || 0) {
                        var b = this.getBBox();
                        bbox.tl_x = Math.floor(Math.min(bbox.tl_x, b.x));
                        bbox.tl_y = Math.floor(Math.min(bbox.tl_y, b.y));
                        bbox.br_x = Math.ceil(Math.max(bbox.br_x, b.x + b.width));
                        bbox.br_y = Math.ceil(Math.max(bbox.br_y, b.y + b.height));
                    }
                });
                var bb_width = bbox.br_x - bbox.tl_x;
                var bb_height = bbox.br_y - bbox.tl_y;
                var scale_factor = Math.max(1, Math.min(width/bb_width, height/bb_height));
                var transform = '';
                transform += 'translate(' + -bbox.tl_x*scale_factor + ',' + -bbox.tl_y*scale_factor + ') ';
                transform += 'scale(' + scale_factor + ') ';
                svg.attr('transform', transform);
            });
        }
    },

    loadData: function(options) {
        var self = this,
            url = app.api.buildURL('Accounts/by_country');

        app.api.call('GET', url, null, {
            success: function(o) {
                self.results = {};
                _(o).each(function(amount, country) {
                    country = self._checkCountry(country);
                    self.results[country] = parseInt(amount, 10);
                });
                if (!self.disposed) self.render();
            },
            complete: options ? options.complete : null
        });
    },

    _checkCountry: function(country) {
        if (country == "USA") {
            country = "United States of America";
        }
        return country;
    }
})
