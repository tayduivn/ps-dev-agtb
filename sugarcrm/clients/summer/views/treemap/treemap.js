({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.guid = _.uniqueId("treemap");
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
        var url = App.api.buildURL("CustomReport/OpportunityByUserStage?oauth_token="+App.api.getOAuthToken());

        var margin = {top: 20, right: 0, bottom: 0, left: 0},
            // TODO: Fix the following
            width = parseInt($("#"+this.guid).width()),
            height = 400,
            formatNumber = d3.format(",d"),
            transitioning;

        var x = d3.scale.linear()
            .domain([0, width])
            .range([0, width]);

        var y = d3.scale.linear()
            .domain([0, height])
            .range([0, height]);

        var treemap = d3.layout.treemap()
            .children(function(d, depth) {
                return depth ? null : d.children;
            }).sort(function(a, b) {
                return a.value - b.value;
            }).round(false);

        var svg = d3.select("#"+self.guid).append("svg")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.bottom + margin.top)
            .style("margin-left", -margin.left+"px")
            .style("margin-right", -margin.right+"px")
          .append("g")
            .attr("transform", "translate("+margin.left + "," + margin.top +  ")")
            .style("shape-rendering", "crispEdges");

        var grandparent = svg.append("g").attr("class", "grandparent");

        grandparent.append("rect")
            .attr("y", -margin.top)
            .attr("width", width)
            .attr("height", margin.top);

        grandparent.append("text")
            .attr("x", 6)
            .attr("y", 6 - margin.top)
            .attr("dy", '.75em');

        d3.json(url, function(root) {
            var nodes = [];

            function initialize(root) {
                root.x = root.y = 0;
                root.dx = width;
                root.dy = height;
                root.depth = 0;
            }

            function accumulate(d) {
                nodes.push(d);
                if(d.children) {
                    return d.value = d.children.reduce(function(p, v) {
                        return p + accumulate(v)
                    }, 0);
                }
                return d.value;
            }

            function layout(d) {
                if(d.children) {
                    treemap.nodes({children: d.children});
                    d.children.forEach(function(c) {
                        c.x = d.x + c.x * d.dx;
                        c.y = d.y + c.y * d.dy;
                        c.dx *= d.dx;
                        c.dy *= d.dy;
                        c.parent = d;
                        layout(c);
                    });
                }
            }

            function display(d) {
                grandparent.datum(d.parent).on("click", transition).select("text").text(name(d));
                var g1 = svg.insert("g", ".grandparent").datum(d).attr("class", "depth");

                var g = g1.selectAll("g").data(d.children).enter().append("g");

                // Transition for nodes with children.
                g.filter(function(d) {
                    return d.children;
                }).classed("children", true).on("click", transition);

                // Navigate for nodes without children (leaves).
                g.filter(function(d) {
                    return !(d.children);
                }).on("click", navigate);

                var child_rects = g.selectAll(".child").data(function(d) {
                    return d.children || [d];
                }).enter().append("rect").attr("class", "child").call(rect);

                var parent_rect = g.append("rect").attr("class", "parent").call(rect)
                    .append("text").text(function(d) {
                        return d.name;
                    });

                var label = g.append("text").attr("dy", ".75em").text(function(d) {
                    return d.name;
                }).call(text);

                function navigate(d) {
                    var model = self.app.data.createBean(self.module);
                    model.set("id", d.id);
                    model.fetch();
                    self.app.navigate(self.context, model);
                }

                function transition(d) {
                    if (transitioning || !d) return;
                    transitioning = true;

                    var g2 = display(d),
                      t1 = g1.transition().duration(750),
                      t2 = g2.transition().duration(750);

                    // Update the domain only after entering new elements.
                    x.domain([d.x, d.x + d.dx]);
                    y.domain([d.y, d.y + d.dy]);

                    // Enable anti-aliasing during the transition.
                    svg.style("shape-rendering", null);

                    // Draw child nodes on top of parent nodes.
                    svg.selectAll(".depth").sort(function(a, b) { return a.depth - b.depth; });

                    // Fade-in entering text.
                    g2.selectAll("text").style("fill-opacity", 0);

                    // Transition to the new view.
                    t1.selectAll("text").call(text).style("fill-opacity", 0);
                    t2.selectAll("text").call(text).style("fill-opacity", 1);
                    t1.selectAll("rect").call(rect);
                    t2.selectAll("rect").call(rect);

                    // Remove the old node when the transition is finished.
                    t1.remove().each("end", function() {
                        svg.style("shape-rendering", "crispEdges");
                        transitioning = false;
                    });
                }

                return g;
            }

            function text(text) {
                text.attr("x", function(d) { return x(d.x) + 6; })
                    .attr("y", function(d) { return y(d.y) + 6; });
            }

            function rect(rect) {
                rect.attr("x", function(d) { return x(d.x); })
                    .attr("y", function(d) { return y(d.y); })
                    .attr("width", function(d) { return x(d.x + d.dx) - x(d.x); })
                    .attr("height", function(d) { return y(d.y + d.dy) - y(d.y); })
                    .attr("class", function(d) {
                        if(d3.select(this).classed(d.class)) {
                            return d3.select(this).attr('class');
                        }
                        return d3.select(this).attr('class') + " " + d.class;
                    });
            }

            function name(d) {
                return d.parent
                    ? name(d.parent) + " / " + d.name
                    : d.name;
            }

            initialize(root);
            accumulate(root);
            layout(root);
            display(root);

        });
    }
})
