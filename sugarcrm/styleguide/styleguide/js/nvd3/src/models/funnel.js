
nv.models.funnel = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 0, right: 0, bottom: 0, left: 0}
    , width = 960
    , height = 500
    , x = d3.scale.ordinal()
    , y = d3.scale.linear()
    , id = Math.floor(Math.random() * 10000) //Create semi-unique ID in case user doesn't select one
    , getX = function(d) { return d.x }
    , getY = function(d) { return d.y }
    , forceY = [0] // 0 is forced by default.. this makes sense for the majority of bar graphs... user can always do chart.forceY([]) to remove
    , stacked = false
    , clipEdge = true
    , delay = 1200
    , xDomain
    , yDomain
    , color = nv.utils.defaultColor()
    , fill = function (d,i) { return color(d,i); }
    , gradient = function (d,i) { return color(d,i); }
    , useClass = false
    , dispatch = d3.dispatch('chartClick', 'elementClick', 'elementDblClick', 'elementMouseover', 'elementMouseout')
    ;

  //============================================================


  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var x0, y0 //used to store previous scales
      ;

  //============================================================

  function chart(selection) {
    selection.each(function(data) {
      var availableWidth = width - margin.left - margin.right
        , availableHeight = height - margin.top - margin.bottom
        , container = d3.select(this)
        , fillGradient = function(d,i) {
            return nv.utils.colorLinearGradient( d, i, 'vertical', color(d,i), wrap.select('defs') );
          }
        ;

      chart.gradient( fillGradient );

      data = d3.layout.stack()
               .offset('zero')
               .values(function(d){ return d.values })
               .y(getY)
               (data);


      //add series index to each data point for reference
      data = data.map(function(series, i) {
        series.values = series.values.map(function(point) {
          point.series = i;
          return point;
        });
        return series;
      });


      //------------------------------------------------------------
      // Setup Scales

      // remap and flatten the data for use in calculating the scales' domains
      var seriesData = (xDomain && yDomain) ? [] : // if we know xDomain and yDomain, no need to calculate
            data.map(function(d) {
              return d.values.map(function(d,i) {
                return { x: getX(d,i), y: getY(d,i), y0: d.y0 }
              })
            });

      x   .domain(d3.merge(seriesData).map(function(d) { return d.x }))
          .rangeBands([0, availableWidth], .1);

      y   .domain(yDomain || d3.extent(d3.merge(seriesData).map(function(d) { return d.y + d.y0 }).concat(forceY)))
          .range([availableHeight, 0]);


      // If scale's domain don't have a range, slightly adjust to make one... so a chart can show a single data point
      if (x.domain()[0] === x.domain()[1] || y.domain()[0] === y.domain()[1]) singlePoint = true;
      if (x.domain()[0] === x.domain()[1])
        x.domain()[0] ?
            x.domain([x.domain()[0] - x.domain()[0] * 0.01, x.domain()[1] + x.domain()[1] * 0.01])
          : x.domain([-1,1]);

      if (y.domain()[0] === y.domain()[1])
        y.domain()[0] ?
            y.domain([y.domain()[0] + y.domain()[0] * 0.01, y.domain()[1] - y.domain()[1] * 0.01])
          : y.domain([-1,1]);


      x0 = x0 || x;
      y0 = y0 || y;

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-funnel').data([data]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-funnel');
      var defsEnter = wrapEnter.append('defs');
      var gEnter = wrapEnter.append('g');
      var g = wrap.select('g');

      gEnter.append('g').attr('class', 'nv-groups');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------
      // Clip Path

      defsEnter.append('clipPath')
          .attr('id', 'nv-edge-clip-' + id)
        .append('rect');
      wrap.select('#nv-edge-clip-' + id + ' rect')
          .attr('width', availableWidth)
          .attr('height', availableHeight);
      g.attr('clip-path', clipEdge ? 'url(#nv-edge-clip-' + id + ')' : '');


      //------------------------------------------------------------

      var w = availableHeight/1.1
        , r = ( ( (w/8) - w ) / 2 ) / availableHeight
        , c = availableWidth/2
        ;


      var groups = wrap.select('.nv-groups').selectAll('.nv-group')
          .data(function(d) { return d }, function(d) { return d.key });
      groups.enter().append('g')
          .style('stroke-opacity', 1e-6)
          .style('fill-opacity', 1e-6);
      d3.transition(groups.exit())
        .selectAll('polygon.nv-bar')
        .delay(function(d,i) { return i * delay/ data[0].values.length })
          .attr('points', function(d) {
              var w0 = (r * y(d.y0)) + w/2, w1 = (r * y(d.y0+d.y)) + w/2;
              return (
                (c - w0) + ',' +  0 + ' ' +
                (c - w1) + ',' +  0 + ' ' +
                (c + w1) + ',' +  0 + ' ' +
                (c + w0) + ',' +  0
              );
            })
          .remove();
      d3.transition(groups.exit())
        .selectAll('text.nv-label-value')
        .delay(function(d,i) { return i * delay/ data[0].values.length })
          .attr('y', 0)
          .remove();
      d3.transition(groups.exit())
        .selectAll('text.nv-label-group')
        .delay(function(d,i) { return i * delay/ data[0].values.length })
          .attr('y', 0)
          .remove();
      groups
          //.attr('class', function(d,i) { return this.getAttribute('class') || 'nv-group nv-series-' + i + ' nv-fill' + (i%20>9?'':'0') + i%20; } )
          .attr('class', function(d,i) {
              return this.getAttribute('class') || (
                'nv-group nv-series-' + i + (
                  useClass
                    ? ( ' '+ ( d.class || 'nv-fill' + (i%20>9?'':'0') + i%20 ) )
                    : ''
                )
              );
          } )
          .classed('hover', function(d) { return d.hover })
          .attr('fill', function(d,i){ return this.getAttribute('fill') || fill(d,i) })
          .attr('stroke', function(d,i){ return this.getAttribute('fill') || fill(d,i) });
      d3.transition(groups)
          .style('stroke-opacity', 1)
          .style('fill-opacity', .85);


      //------------------------------------------------------------
      // Polygons

      var funs = groups.selectAll('polygon.nv-bar')
          .data(function(d) { return d.values });

      funs.exit().remove();

      var funsEnter = funs.enter()
          .append('polygon')
            .attr('class', 'nv-bar positive')
            .attr('points', function(d) {
              var w0 = (r * y(d.y0)) + w/2, w1 = (r * y(d.y0+d.y)) + w/2;
              return (
                (c - w0) + ',' +  0 + ' ' +
                (c - w1) + ',' +  0 + ' ' +
                (c + w1) + ',' +  0 + ' ' +
                (c + w0) + ',' +  0
              );
            });

      d3.transition(funs)
          .delay(function(d,i) { return i * delay / data[0].values.length })
          .attr('points', function(d) {
            var w0 = (r * y(d.y0)) + w/2, w1 = (r * y(d.y0+d.y)) + w/2;
            return (
              (c - w0) + ',' +  y(d.y0) + ' ' +
              (c - w1) + ',' +  y(d.y0+d.y) + ' ' +
              (c + w1) + ',' +  y(d.y0+d.y) + ' ' +
              (c + w0) + ',' +  y(d.y0)
            );
          });
          // .each('end', function() {
          //   d3.transition(d3.select(this))
          //     .attr('width', x.rangeBand() );
          // })

      // funs
      //     .attr('class', 'nv-bar positive')
      //     .attr('transform', function(d,i) { return 'translate(' + x(getX(d,i)) + ',0)'; });


      //------------------------------------------------------------
      // Value Labels

      var lblValue = groups.selectAll('text.nv-label-value')
          .data( function(d) { return d.values } );

      lblValue.exit().remove();

      var lblValueEnter = lblValue.enter()
          .append('text')
            .attr('class', 'nv-label-value')
            .attr('x', function(d) {
              return c - (r * y(d.y0)) - w/2;
            })
            .attr('y', 0 )
            .attr('text-anchor', 'end')
            .text(function(d) { return '$' + d.y + 'K' })
            .attr('stroke', 'none')
            .attr('fill', 'black')
            .attr('dx', -20)
            .attr('dy', '-.5em')
          ;

      d3.transition(lblValue)
          .delay(function(d,i) { return i * delay / data[0].values.length })
          .attr('x', function(d) {
              return c - (r * y(d.y0)) - w/2;
            })
          .attr('y', function(d) { return y(d.y0) } );

      //------------------------------------------------------------
      // Group Labels

      var lblGroup = groups.selectAll('text.nv-label-group')
          .data( function(d) { return [ { y0: d.values[0].y0, key: d.key } ] } );

      lblGroup.exit().remove();

      var lblGroupEnter = lblGroup.enter()
          .append('text')
            .attr('class', 'nv-label-group')
            .attr('x', function(d) {
              return c + (r * y(d.y0)) + w/2;
            })
            .attr('y', 0 )
            .attr('text-anchor', 'start')
            .text(function(d) { return d.key })
            .attr('stroke', 'none')
            .attr('fill', 'black')
            .attr('dx', 20)
            .attr('dy', '-.5em')
          ;

      d3.transition(lblGroup)
          .delay(function(d,i) { return i * delay / data[0].values.length })
          .attr('x', function(d) {
              return c + (r * y(d.y0)) + w/2;
            })
          .attr('y', function(d) { return y(d.y0) } );

      //------------------------------------------------------------

      funs
          .on('mouseover', function(d,i) { //TODO: figure out why j works above, but not here
            d3.select(this).classed('hover', true);
            dispatch.elementMouseover({
              value: getY(d,i),
              point: d,
              series: data[d.series],
              pos: [x(getX(d,i)) + ( x.rangeBand() * (data.length / 2) / data.length ), y(getY(d,i) + d.y0)],  // TODO: Figure out why the value appears to be shifted
              pointIndex: i,
              seriesIndex: d.series,
              e: d3.event
            });
          })
          .on('mouseout', function(d,i) {
            d3.select(this).classed('hover', false);
            dispatch.elementMouseout({
              value: getY(d,i),
              point: d,
              series: data[d.series],
              pointIndex: i,
              seriesIndex: d.series,
              e: d3.event
            });
          })
          .on('click', function(d,i) {
            dispatch.elementClick({
              value: getY(d,i),
              point: d,
              series: data[d.series],
              pos: [x(getX(d,i)) + ( x.rangeBand() * (data.length / 2) / data.length ), y(getY(d,i) + d.y0)],  // TODO: Figure out why the value appears to be shifted
              pointIndex: i,
              seriesIndex: d.series,
              e: d3.event
            });
            d3.event.stopPropagation();
          })
          .on('dblclick', function(d,i) {
            dispatch.elementDblClick({
              value: getY(d,i),
              point: d,
              series: data[d.series],
              pos: [x(getX(d,i)) + ( x.rangeBand() * (data.length / 2) / data.length ), y(getY(d,i) + d.y0)],  // TODO: Figure out why the value appears to be shifted
              pointIndex: i,
              seriesIndex: d.series,
              e: d3.event
            });
            d3.event.stopPropagation();
          });


      //store old scales for use in transitions on update
      x0 = x.copy();
      y0 = y.copy();

    });

    return chart;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  chart.dispatch = dispatch;

  chart.color = function(_) {
    if (!arguments.length) return color;
    color = _;
    return chart;
  };
  chart.fill = function(_) {
    if (!arguments.length) return fill;
    fill = _;
    return chart;
  };
  chart.gradient = function(_) {
    if (!arguments.length) return gradient;
    gradient = _;
    return chart;
  };
  chart.useClass = function(_) {
    if (!arguments.length) return useClass;
    useClass = _;
    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) return getX;
    getX = _;
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) return getY;
    getY = _;
    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) return margin;
    margin = _;
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) return width;
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) return height;
    height = _;
    return chart;
  };

  chart.xScale = function(_) {
    if (!arguments.length) return x;
    x = _;
    return chart;
  };

  chart.yScale = function(_) {
    if (!arguments.length) return y;
    y = _;
    return chart;
  };

  chart.xDomain = function(_) {
    if (!arguments.length) return xDomain;
    xDomain = _;
    return chart;
  };

  chart.yDomain = function(_) {
    if (!arguments.length) return yDomain;
    yDomain = _;
    return chart;
  };

  chart.forceY = function(_) {
    if (!arguments.length) return forceY;
    forceY = _;
    return chart;
  };

  chart.stacked = function(_) {
    if (!arguments.length) return stacked;
    stacked = _;
    return chart;
  };

  chart.id = function(_) {
    if (!arguments.length) return id;
    id = _;
    return chart;
  };

  chart.delay = function(_) {
    if (!arguments.length) return delay;
    delay = _;
    return chart;
  };

  chart.clipEdge = function(_) {
    if (!arguments.length) return clipEdge;
    clipEdge = _;
    return chart;
  };

  //============================================================


  return chart;
}
