
nv.models.multiBarChart = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 30, right: 20, bottom: 50, left: 60}
    , width = null
    , height = null
    , getX = function(d) { return d.x }
    , getY = function(d) { return d.y }
    , color = nv.utils.defaultColor()
    , showControls = true
    , showLegend = true
    , reduceXTicks = false // if false a tick will show for every data point
    , reduceYTicks = false // if false a tick will show for every data point
    , rotateLabels = -15
    , tooltips = true
    , tooltipBar = function(key, x, y, e, graph) {
        return '<p>Stage: <b>' + key + '</b></p>' +
               '<p>Amount: <b>$' +  parseInt(y) + 'K</b></p>' +
               '<p>Percent: <b>' +  x + '%</b></p>'
      }
    , tooltipLine = function(key, x, y, e, graph) {
        return '<p>Likely: <b>' + parseInt(y) + 'K</b></p>'
      }
    //, x //can be accessed via chart.xScale()
    //, y //can be accessed via chart.yScale()
    , noData = "No Data Available."
    ;

  var lines = nv.models.line()
    , bars = nv.models.multiBar().stacked(true)
    //, x = d3.scale.linear() // needs to be both line and historicalBar x Axis
    , x = bars.xScale()
    , y1 = bars.yScale()
    , y2 = lines.yScale()
    , xAxis = nv.models.axis().scale(x).orient('bottom').tickPadding(5).tickFormat(function(d) { return d })
    , yAxis1 = nv.models.axis().scale(y1).orient('left').tickFormat(function(d) { return d + 'K' })
    , yAxis2 = nv.models.axis().scale(y2).orient('right').tickFormat(d3.format(',.1f'))
    , legend = nv.models.legend()
    , controls = nv.models.legend()
    , dispatch = d3.dispatch('tooltipShow', 'tooltipHide')
    ;

  xAxis
    .highlightZero(false)
    .showMaxMin(false)
    ;

  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var showTooltip = function(e, offsetElement, groupTotals) {
    var left = e.pos[0] + ( offsetElement.offsetLeft || 0 ),
        top = e.pos[1] + ( offsetElement.offsetTop || 0),
        //x = xAxis.tickFormat()( lines.x()(e.point, e.pointIndex) ),
        x = (e.point.y * 100 / groupTotals[e.pointIndex].t).toFixed(1)
        y = ( e.series.type==='bar' ? yAxis1 : yAxis2 ).tickFormat()( lines.y()(e.point, e.pointIndex) ),
        content = ( e.series.type==='bar' ? tooltipBar(e.series.key, x, y, e, chart) : tooltipLine(e.series.key, x, y, e, chart) );

    nv.tooltip.show([left, top], content, e.value < 0 ? 'n' : 's', null, offsetElement);
  };

  var barClick = function(data,e,selection) {
    //if only one bar series is disabled
    if (data.filter(function(d) { return !d.disabled && d.type==='bar' }).length === 1)
      // reenable the disabled bar series
      data = data.map(function(d) {
        d.disabled = false;
        return d
      });
    else
      // hide the selected bar series
      data = data.filter(function(d) { return d.type==='bar' }).map( function(d,i) {
        d.disabled = (i != e.seriesIndex);
        return d
      });

    selection.transition().call(chart);
  };

  //============================================================


  function chart(selection) {
    selection.each(function(data) {
      var container = d3.select(this),
          that = this;

      var availableWidth = (width  || parseInt(container.style('width')) || 960)
                             - margin.left - margin.right,
          availableHeight = (height || parseInt(container.style('height')) || 400)
                             - margin.top - margin.bottom;

      //------------------------------------------------------------
      // Display noData message if there's nothing to show.

      if (!data || !data.length || !data.filter(function(d) { return d.values.length }).length) {
        container.append('text')
          .attr('class', 'nvd3 nv-noData')
          .attr('x', availableWidth / 2)
          .attr('y', availableHeight / 2)
          .attr('dy', '-.7em')
          .style('text-anchor', 'middle')
          .text(noData);
          return chart;
      } else {
        container.select('.nv-noData').remove();
      }

      //------------------------------------------------------------


      var dataBars = data.filter(function(d) { return !d.disabled && d.type==='bar' });

      var dataLines = data.filter(function(d) { return !d.disabled && d.type==='line' });

      var groupLabels = data.filter(function(d) { return !d.disabled && d.type==='chart' })[0].labels;

      var groupTotals = data.filter(function(d) { return !d.disabled && d.type==='chart' })[0].values;

      var quotaValue = data.filter(function(d) { return !d.disabled && d.type==='chart' })[0].quota;

      //TODO: try to remove x scale computation from this layer

      var series1 = data.filter(
            function(d) { 
              return !d.disabled && d.type==='bar' 
            }
          ).map(
            function(d) {
              return d.values.map(
                function(d,i) {
                  return { x: getX(d,i), y: getY(d,i) }
                }
              )
            }
        );

      var series2 = data.filter(function(d) { return !d.disabled && d.type==='line' })
            .map(function(d) {
              return d.values.map(function(d,i) {
                return { x: getX(d,i), y: getY(d,i) }
              })
            });

      x   .domain(d3.extent(d3.merge(series1.concat(series2)), function(d) { return d.x } ))
          .range([0, availableWidth]);
      
      //x   .domain(d3.extent(d3.merge(data.map(function(d) { return d.values })), getX ))
      //   .range([0, availableWidth]);

      //y1  .domain(d3.extent(d3.merge(dataBars), function(d) { return d.y } ))
      //    .range([availableHeight, 0]);

      //y2  .domain(d3.extent(d3.merge(dataLines), function(d) { return d.y } ))
      //    .range([availableHeight, 0]);
      

      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-multiBarWithLegend').data([data]);
      var gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-multiBarWithLegend').append('g');
      var g = wrap.select('g');

      gEnter.append('g').attr('class', 'nv-x nv-axis');
      gEnter.append('g').attr('class', 'nv-y1 nv-axis');
      gEnter.append('g').attr('class', 'nv-y2 nv-axis');
      gEnter.append('g').attr('class', 'nv-barsWrap');
      gEnter.append('g').attr('class', 'nv-linesWrap');
      gEnter.append('g').attr('class', 'nv-legendWrap');
      gEnter.append('g').attr('class', 'nv-controlsWrap');
      gEnter.append('g').attr('class', 'nv-quotaWrap');

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Legend

      if (showLegend) {
        legend.width(availableWidth);

        g.select('.nv-legendWrap')
            .datum( 
              //data
              data.filter(function(d) { 
                //return !d.disabled 
                return d.type==='bar' //TODO: breaks legend control
              }) 
            )
            .call(legend);

        if ( margin.top != legend.height()) {
          margin.top = legend.height();
          availableHeight = (height || parseInt(container.style('height')) || 400)
                             - margin.top - margin.bottom;
        }

        g.select('.nv-legendWrap')
            .attr('transform', 'translate(0,' + (-margin.top) +')');
      }


      //------------------------------------------------------------
      // Controls

      if (showControls) {
        var controlsData = [
          { key: 'Grouped', disabled: bars.stacked() },
          { key: 'Stacked', disabled: !bars.stacked() }
        ];

        controls.width(180).color(['#444', '#444', '#444']);
        g.select('.nv-controlsWrap')
            .datum(controlsData)
            .attr('transform', 'translate(0,' + (-margin.top) +')')
            .call(controls);
      }

      //------------------------------------------------------------


      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');


      //------------------------------------------------------------
      // Main Chart Component(s)


      lines
        .width(availableWidth)
        .height(availableHeight)
        .color(
          data.map( function(d,i) {
            return d.color || color(d, i);
          } ).filter(function(d,i) { return !data[i].disabled && data[i].type==='line' } )
        )
        .forceY([0,350])
        .forceX([.455,5.545])

      bars
        .width(availableWidth)
        .height(availableHeight)
        .color(   
          data.map( function(d,i) {
            return d.color || color(d, i);
          } ).filter( function(d,i) { return !data[i].disabled && data[i].type==='bar' } )  
        )
        .forceY([0,350])
        ;

      var barsWrap = g.select('.nv-barsWrap')
          .datum(dataBars.length ? dataBars : [{values:[]}])

      var linesWrap = g.select('.nv-linesWrap')
          .datum(  dataLines.length ? dataLines : [{values:[]}]  );

      d3.transition(barsWrap).call(bars);
      d3.transition(linesWrap).call(lines);


      //------------------------------------------------------------
      // Quota Line

      g.selectAll('line.nv-quotaLine').remove();

      g.select('.nv-quotaWrap').append('line')
        .attr('class', 'nv-quotaLine')
        .attr('x1', 0)
        .attr('y1', 0)
        .attr('x2', availableWidth)
        .attr('y2', 0)
        .attr('transform', 'translate(0,' + y1(quotaValue) +')')
        .style('stroke-dasharray','20, 5');

      //------------------------------------------------------------
      // Setup Axes

      xAxis
        .ticks( availableWidth / 100 )
        .tickSize(-availableHeight, 0)
        //.tickFormat(function(d) { return d })
        .tickFormat(function(d,i) {
          return groupLabels[i] ? groupLabels[i].l : 'asfd'
        });

      g.select('.nv-x.nv-axis')
          .attr('transform', 'translate(0,' + y1.range()[0] + ')');
      d3.transition(g.select('.nv-x.nv-axis'))
          .call(xAxis);

      var xTicks = g.select('.nv-x.nv-axis > g').selectAll('g');

      xTicks
          .selectAll('line, text')
          .style('opacity', 1)

      if (reduceXTicks)
        xTicks
          .filter(function(d,i) {
              return i % Math.ceil(data[0].values.length / (availableWidth / 100)) !== 0;
            })
          .selectAll('text, line')
          .style('opacity', 0);

      if (rotateLabels)
      {
        xTicks
            .selectAll('text')
            .attr('transform', function(d,i,j) { return 'rotate('+rotateLabels+' 0,0) translate(0,10)' })
            .attr('text-transform', rotateLabels > 0 ? 'start' : 'end');
        // xTicks
        //     .selectAll('text')
        //     .attr('transform', 'translate(0,10)');
      }

      yAxis1
        .ticks( availableHeight / 100 )
        .tickSize(-availableWidth, 0);

      d3.transition(g.select('.nv-y1.nv-axis'))
          .style('opacity', dataBars.length ? 1 : 0)
          .call(yAxis1);

      // yAxis2
      //   .ticks( availableHeight / 36 )
      //   .tickSize(dataBars.length ? 0 : -availableWidth, 0); // Show the y2 rules only if y1 has none

      // d3.transition(g.select('.nv-y2.nv-axis'))
      //     .call(yAxis2);

      //------------------------------------------------------------



      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      legend.dispatch.on('legendClick', function(d,i) {
        d.disabled = !d.disabled;

        if (!data.filter(function(d) { return !d.disabled  && d.type==='bar' }).length) {
          data.map(function(d) {
            d.disabled = false;
            wrap.selectAll('.nv-series').classed('disabled', false);
            return d;
          });
        }

        selection.transition().call(chart);
      });

      controls.dispatch.on('legendClick', function(d,i) {
        if (!d.disabled) return;
        controlsData = controlsData.map(function(s) {
          s.disabled = true;
          return s;
        });
        d.disabled = false;

        switch (d.key) {
          case 'Grouped':
            bars.stacked(false);
            break;
          case 'Stacked':
            bars.stacked(true);
            break;
        }

        selection.transition().call(chart);
      });

      /*dispatch.on('tooltipShow', function(e) { 
        if (tooltips) showTooltip(e, that.parentNode) 
      });*/

      lines.dispatch.on('elementMouseover.tooltip', function(e) {
        e.pos = [e.pos[0] +  margin.left, e.pos[1] + margin.top];
        dispatch.tooltipShow(e);
      });

      lines.dispatch.on('elementMouseout.tooltip', function(e) {
        dispatch.tooltipHide(e);
      });


      bars.dispatch.on('elementMouseover.tooltip', function(e) {
        e.pos = [e.pos[0] +  margin.left, e.pos[1] + margin.top];
        dispatch.tooltipShow(e);
      });

      bars.dispatch.on('elementMouseout.tooltip', function(e) {
        dispatch.tooltipHide(e);
      });

      bars.dispatch.on('elementClick', function(e) {
        //console.log(data.filter(function(d) { return !d.disabled }))
        barClick(data,e,selection);
      });

      if (tooltips) dispatch.on('tooltipShow', function(e) { showTooltip(e, that.parentNode, groupTotals) } ); // TODO: maybe merge with above?
      if (tooltips) dispatch.on('tooltipHide', nv.tooltip.cleanup);

      //============================================================


      chart.update = function() { selection.transition().call(chart) };
      chart.container = this; // I need a reference to the container in order to have outside code check if the chart is visible or not

    });

    return chart;
  }


  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  /*bars.dispatch.on('elementMouseover.tooltip2', function(e) {
    e.pos = [e.pos[0] +  margin.left, e.pos[1] + margin.top];
    dispatch.tooltipShow(e);
  });

  bars.dispatch.on('elementMouseout.tooltip', function(e) {
    dispatch.tooltipHide(e);
  });
  dispatch.on('tooltipHide', function() {
    if (tooltips) nv.tooltip.cleanup();
  });*/

  //============================================================


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.lines = lines;
  chart.bars = bars;
  chart.legend = legend;
  chart.xAxis = xAxis;
  chart.yAxis1 = yAxis1;
  chart.yAxis2 = yAxis2;

  d3.rebind(chart, lines, bars, 'x', 'y', 'xDomain', 'yDomain', 'forceX', 'forceY', 'clipEdge', 'id', 'stacked', 'delay');

  //d3.rebind(chart, lines, 'interactive');
  //consider rebinding x and y as well

  chart.x = function(_) {
    if (!arguments.length) return getX;
    getX = _;
    lines.x(_);
    bars.x(_);
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) return getY;
    getY = _;
    lines.y(_);
    bars.y(_);
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

  chart.color = function(_) {
    if (!arguments.length) return color;
    color = nv.utils.getColor(_);
    legend.color(color);
    return chart;
  };

  chart.showControls = function(_) {
    if (!arguments.length) return showControls;
    showControls = _;
    return chart;
  };

  chart.showLegend = function(_) {
    if (!arguments.length) return showLegend;
    showLegend = _;
    return chart;
  };

  chart.reduceXTicks= function(_) {
    if (!arguments.length) return reduceXTicks;
    reduceXTicks = _;
    return chart;
  };

  chart.rotateLabels = function(_) {
    if (!arguments.length) return rotateLabels;
    rotateLabels = _;
    return chart;
  }

  chart.tooltipBar = function(_) {
    if (!arguments.length) return tooltipBar;
    tooltipBar = _;
    return chart;
  };

  chart.tooltipLine = function(_) {
    if (!arguments.length) return tooltipLine;
    tooltipLine = _;
    return chart;
  };

  chart.tooltips = function(_) {
    if (!arguments.length) return tooltips;
    tooltips = _;
    return chart;
  };

  chart.tooltipContent = function(_) {
    if (!arguments.length) return tooltip;
    tooltip = _;
    return chart;
  };

  chart.noData = function(_) {
    if (!arguments.length) return noData;
    noData = _;
    return chart;
  };

  chart.barClick = function(_) {
    if (!arguments.length) return barClick;
    barClick = _;
    return chart;
  };

  //============================================================


  return chart;
}
