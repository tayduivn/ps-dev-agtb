({
    className: 'block',
    results: {},
    plugins: ['Dashlet'],
    dragging: false,

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
    },

    _renderHtml: function() {
        var self = this,
            results = this.results;

        // Set constants for the map.
        var margin = {top: 10, right: 10, bottom: 10, left: 10},
            usa_view = {rotate:[-97,38], scale:3};

        // Calculate the maximum balue so that we can generate a colour scale.
        var max = _.max(_.pluck(results, '_total')),
            color = d3.scale.linear().domain([0, max]).range(['#999', '#336699']);


        app.view.View.prototype._renderHtml.call(this);

        var m0,
            o0,
            t0,
            globe,
            world_data = [],
            country_data = {},
            active_country = false,
            current_view = {rotate:[0,0], scale:1},
            tooltips = true,
            node = this.$('svg'),
            width = parseInt(node.width(), 10) - margin.left - margin.right,
            height = parseInt(node.height(), 10) - margin.top - margin.top,
            dispatch = d3.dispatch('tooltipShow', 'tooltipHide'),
            tooltip = function(d) {
                return '<p><b>' + d.name + '</b></p>' +
                    '<p><b>Amount:</b> $' +  d3.format(',.2f')(d.amount) + '</p>';
            };

        //============================================================

        var projection = d3.geo.azimuthal()
              .scale(Math.min(height/2,width/2))
              .origin([-103,10])
              .mode('orthographic')
              .translate([width/2+margin.left, height/2+margin.top]);

        var circle = d3.geo.greatCircle()
              .origin([-103,10]);

        var path = d3.geo.path()
              .projection(projection);

        var svg = d3.select("svg#" + this.cid)
            .attr('width', width)
            .attr('height', height)
            .on('mousedown', mousedown);

        var backgroundCircle = svg.append('circle')
            .attr('cx', width / 2)
            .attr('cy', height / 2)
            .attr('r', projection.scale())
            .attr('class', 'globe')
            .attr('transform', 'translate('+ margin.left +','+ margin.top +')');

        // zoom and pan
        var zoom = d3.behavior.zoom()
            .on('zoom', function () {
                projection.scale((Math.min(height,width)*d3.event.scale)/2);
                backgroundCircle.attr('r', projection.scale());
                refresh();
            });

        // Set clipping path function for the great circle.
        var clip = function (d) {
            return path(circle.clip(d)) || 'M0,0Z';
        };

        var region_results = function(d) {
            if (results[d.id]) {
                return results[d.id];
            } else if (results[d.properties.name]) {
                return results[d.properties.name];
            }
        };

        var amount = function(d) {
            var result = region_results(d);
            if (result) {
                return result._total;
            }
            return 0;
        };

        svg.call(zoom);

        backgroundCircle.on('click', function () {
            unLoadCountry();
        });

        d3.json(app.config.siteUrl + "/clients/base/views/countrychart/world-countries.json", function (collection) {
            world_data = collection.features;

            loadChart(world_data);
        });

        d3.json(app.config.siteUrl + "/clients/base/views/countrychart/us-states.json", function (collection) {
            country_data['USA'] = collection.features;
        });

        function loadChart(data) {
            globe = svg.selectAll('path').data(data);
            globe.exit().remove();
            globe.enter().append('svg:path')
                .attr('d', clip)
                .style('fill', function (d) {
                    var value = amount(d);
                    return color(value);
                });

            globe.on('click', function (d) {
                if (active_country != d3.select(this)) {
                    unLoadCountry();
                    // If we have country-specific geographic features.
                    if (country_data[d.id]) {
                        current_view = {
                            rotate: projection.origin(),
                            scale: projection.scale()
                        };

                        // Flatten the results and include the state-level
                        // results so that we don't need complex tooltip logic.
                        var obj = _.extend(region_results(d), results,  {
                            parent: results
                        });
                        results = obj;

                        if (tooltips) nv.tooltip.cleanup();
                        active_country = d3.select(this);
                        loadChart(world_data.concat(country_data[d.id]));
                        active_country.style('display','none');
                        rotate(usa_view.rotate);
                        projection.scale(Math.min(height,width)*usa_view.scale/2);
                        backgroundCircle.attr('r', projection.scale());
                        refresh();
                    }
                }
            });

            globe.on('mouseover', function (d) {
                mouseover(d);
            });

            globe.on('mouseout', function () {
                mouseout();
            });
        }

        function unLoadCountry() {
            if (active_country) {
                results = results.parent;
                active_country.style('display','inline');
                loadChart(world_data);
                active_country = false;
                rotate(current_view.rotate);
                projection.scale(current_view.scale);
                backgroundCircle.attr('r', projection.scale());
                refresh();
            }
        }

        var showTooltip = function(e, offsetElement) {
            // New addition to calculate position if SVG is scaled with viewBox, may move TODO: consider implementing everywhere else
            var offsets = {left:0,right:0};

            if (offsetElement) {
                var svg = d3.select(offsetElement).select('svg'),
                    viewBox = svg.attr('viewBox');
                offsets = nv.utils.getAbsoluteXY(offsetElement);
                if (viewBox) {
                    viewBox = viewBox.split(' ');
                    var ratio = parseInt(svg.style('width'),10) / viewBox[2];
                    e.pos[0] = e.pos[0] * ratio;
                    e.pos[1] = e.pos[1] * ratio;
                }
            }

            var left = e.pos[0] + ( offsets.left || 0 ) + margin.left,
                top = e.pos[1] + ( offsets.top || 0) + margin.top,
                content = tooltip(e);

            nv.tooltip.show([left, top], content, null, null, offsetElement);
        };

        function mouseover(d) {
            if (tooltips) {
                var evnt = {
                    pos:[d3.event.pageX,d3.event.pageY],
                    name: d.properties.name,
                    amount: amount(d)
                };
                showTooltip(evnt, d3.select("svg#" + this.cid).parentNode);
            }
        }

        function mouseout() {
          if (tooltips) nv.tooltip.cleanup();
        }

        function mousedown() {
            m0 = [d3.event.pageX, d3.event.pageY];
            o0 = projection.origin();
            d3.event.preventDefault();
        }

        function mousemove() {
            if (m0) {
                var m1 = [d3.event.pageX, d3.event.pageY],
                //o1 = [o0[0] + (m0[0] - m1[0]) / 4, o0[1] - (m0[1] - m1[1]) / 4];
                o1 = [o0[0] + (m0[0] - m1[0]) / 4, 10];
                rotate(o1);
            }
        }

        function mouseup() {
            if (m0) {
                mousemove();
                m0 = null;
            }
        }

        function refresh(duration) {
            (duration ? globe.transition().duration(20) : globe).attr('d', clip);
        }


        function autoSpin() {
            var o0 = projection.origin(),
                m1 = [10, 0],
                o1 = [o0[0] - m1[0]/8, 10];
            rotate(o1);
        }

        function rotate(o) {
            projection.origin(o);
            circle.origin(projection.origin());
            refresh();
        }

        // Must select window, in case the user lets go of the mouse outside the
        // dashlet's pane.
        d3.select(window)
            .on('mousemove', mousemove)
            .on('mouseup', mouseup);
    },

    loadData: function(options) {
        options = options || {};
        var self = this,
            url = app.api.buildURL('Accounts', 'by_country');

        app.api.call('GET', url, null, {
            success: function(o) {
                self.results = o;
                if (!self.disposed) self.render();
            },
            complete: options ? options.complete : null
        });
    }
})
