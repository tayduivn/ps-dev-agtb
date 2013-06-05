({
    className: 'block',
    results: {},
    plugins: ['Dashlet'],
    dragging: false,

    _renderHtml: function() {
        var self = this,
            results = this.results,
            svgId = 'svg#' + this.cid,
            autoSpin = false,
            // Set constants for the map.
            margin = {top: 10, right: 10, bottom: 10, left: 10};

        // Calculate the maximum balue so that we can generate a colour scale.
        var colorRange = function(r) {
                var max = _.max(_.pluck(r, '_total'));
                return d3.scale.linear().domain([0, max]).range(['#99CCFF', '#336699']);
            },
            color = colorRange(results),
            fill = function(d) {
                var r = amount(d);
                return r ? color(r) : '';
            };


        app.view.View.prototype._renderHtml.call(this);

        var m0,
            o0,
            t0,
            countries,
            active_country = false,
            world_map = [],
            country_map = {},
            country_label = {},
            world_view = {rotate:[100, -10], scale:1, zoom:1},
            country_view = {rotate:[null, null], scale:null, zoom:null},
            node = this.$(svgId),
            width = parseInt(node.width(), 10) - margin.left - margin.right,
            height = parseInt(node.height(), 10) - margin.top - margin.top,
            iRotation,
            dispatch = d3.dispatch('tooltipShow', 'tooltipHide'),
            tooltips = true,
            tooltip = null,
            tooltipContent = function(d) {
                return '<p><b>' + d.name + '</b></p>' +
                    '<p><b>Amount:</b> $' +  d3.format(',.0f')(d.amount) + '</p>';
            };

        //============================================================

        var projection = d3.geo.orthographic()
              .scale(Math.min(height, width) * 1 / 2)
              .translate([width / 2 + margin.left, height / 2 + margin.top])
              .clipAngle(90)
              .precision(0.1)
              .rotate(world_view.rotate);

        var path = d3.geo.path()
              .projection(projection);

        var graticule = d3.geo.graticule();

        var svg = d3.select(svgId)
              .attr('width', width)
              .attr('height', height)
              .on('mousedown', mousedown);

            svg.append('path')
                .datum({type: 'Sphere'})
                .attr('class', 'sphere')
                .attr('d', path);

        // zoom and pan
        var zoom = d3.behavior.zoom()
            .on('zoom', function () {
                projection.scale((Math.min(height, width)*Math.min(Math.max(d3.event.scale, 0.75), 3)) / 2);
                refresh();
            });

        svg.call(zoom);


        d3.json(app.config.siteUrl + '/styleguide/assets/js/nvd3/data/cldr_fr.json', function (labels) {
            country_label = labels;
        });

        d3.json(app.config.siteUrl + '/styleguide/assets/js/nvd3/data/ne_110m_admin_0_countries.json', function (world) {
            world_map = topojson.feature(world, world.objects.countries).features;
            loadChart(world_map, 'countries');
            if (autoSpin) {
                iRotation = setInterval(spin, 10);
            }
        });

        d3.json(app.config.siteUrl + '/styleguide/assets/js/nvd3/data/us-states.json', function (country) {
            country_map['USA'] = country.features;
        });

        d3.select('.sphere')
          .on('click', function(){
            unLoadCountry();
          });

        function loadChart(data, classes) {

            countries = svg.append('g')
                .attr('class', classes)
              .selectAll('path')
                .data(data)
              .enter().append('path')
                .attr('d', clip)
                .style('fill', fill);

            countries.on('click', function (d) {
                if (active_country != d3.select(this)) {

                    unLoadCountry();

                    // If we have country-specific geographic features.
                    if (country_map[d.id]) {
                        if (tooltips) nv.tooltip.cleanup();

                        world_view = {
                            rotate: projection.rotate(),
                            scale: projection.scale(),
                            zoom: zoom.scale()
                        };

                        var centroid = d3.geo.centroid(d);
                        projection.rotate([-centroid[0], -centroid[1]]);

                        var bounds = path.bounds(d);
                        var hscale = width  / (bounds[1][0] - bounds[0][0]);
                        var vscale = height / (bounds[1][1] - bounds[0][1]);

                        if (width * hscale < height * vscale) {
                            projection.scale(width * hscale / 2);
                            zoom.scale(hscale);
                        } else {
                            projection.scale(height * vscale / 2);
                            zoom.scale(vscale);
                        }

                        country_view = {
                            rotate: projection.rotate(),
                            scale: projection.scale(),
                            zoom: zoom.scale()
                        };

                        // Flatten the results and include the state-level
                        // results so that we don't need complex tooltip logic.
                        var obj = _.extend(region_results(d), {
                            parent: results
                        });
                        results = obj;

                        color = colorRange(results);

                        active_country = d3.select(this);

                        loadChart(country_map[d.id], 'states');

                        active_country.style('display','none');

                        refresh();
                    }
                }
            });

            countries.on('mouseover', function (d) {
                mouseover(d);
            });

            countries.on('mouseout', function () {
                mouseout();
            });
        }

        function unLoadCountry() {
            if (active_country) {
                results = results.parent;
                active_country.style('display', 'inline');
                color = colorRange(results);
                d3.select('.states').remove();
                active_country = false;
                country_view = {rotate:[null, null], scale:null, zoom:null};
                projection.rotate(world_view.rotate);
                projection.scale(world_view.scale);
                zoom.scale(world_view.zoom);
                refresh();
            }
        }

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

        var showTooltip = function(e, offsetElement) {
            // New addition to calculate position if SVG is scaled with viewBox, may move TODO: consider implementing everywhere else
            var offsets = {left:0, right:0};

            if (offsetElement) {
                var svg = d3.select(offsetElement).select('svg'),
                    viewBox = svg.attr('viewBox');
                offsets = nv.utils.getAbsoluteXY(offsetElement);
                if (viewBox) {
                    viewBox = viewBox.split(' ');
                    var ratio = parseInt(svg.style('width'), 10) / viewBox[2];
                    e.pos[0] = e.pos[0] * ratio;
                    e.pos[1] = e.pos[1] * ratio;
                }
            }

            var left = e.pos[0] + ( offsets.left || 0 ) + margin.left,
                top = e.pos[1] + ( offsets.top || 0) + margin.top,
                content = tooltipContent(e);

            tooltip = nv.tooltip.show([left, top], content, null, null, offsetElement);
        };

        function mouseover(d) {
            if (tooltips) {
                var evnt = {
                    pos:[d3.event.pageX, d3.event.pageY],
                    name: (country_label[d.properties.iso_a2] || d.properties.name),
                    amount: amount(d)
                };
                showTooltip(evnt, svg.parentNode);
            }
        }

        function mouseout() {
            if (tooltips) nv.tooltip.cleanup();
        }

        function mousedown() {
            m0 = [d3.event.pageX, d3.event.pageY];
            o0 = projection.rotate();
            d3.event.preventDefault();
            if (tooltips) nv.tooltip.cleanup();
            if (autoSpin) {
                clearInterval(iRotation);
            }
        }

        function mousemove() {
            if (tooltip) {
                var pos = [d3.event.pageX, d3.event.pageY];
                nv.tooltip.position(tooltip, pos);
            }
            if (m0) {
                var m1 = [d3.event.pageX, d3.event.pageY],
                    //o1 = [o0[0] + (m0[0] - m1[0]) / 4, o0[1] - (m0[1] - m1[1]) / 4];
                    o1 = [o0[0] - (m0[0] - m1[0]) / 4, (country_view.rotate[1] || world_view.rotate[1])];
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
            svg.selectAll('path').attr('d', clip);
        }

        function clip(d) {
          return path(d) || 'M0,0Z';
        }

        function spin() {
            var o0 = projection.rotate(),
                m1 = [10, 0],
                o1 = [o0[0] + m1[0] / 8, -10];
            rotate(o1);
        }

        function rotate(o) {
            projection.rotate(o);
            refresh();
        }

        function resize() {
            width = parseInt(node.width(), 10) - margin.left - margin.right;
            height = parseInt(node.height(), 10) - margin.top - margin.top;
            projection
                .scale((Math.min(height,width) * Math.min(Math.max(zoom.scale(), 0.75), 3)) / 2)
                .translate([width / 2 + margin.left, height / 2 + margin.top]);
            refresh();
        }

        // Must select window, in case the user lets go of the mouse outside the
        // dashlet's pane.
        d3.select(window)
            .on('mousemove', mousemove)
            .on('mouseup', mouseup);

        nv.utils.windowResize(resize);
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
