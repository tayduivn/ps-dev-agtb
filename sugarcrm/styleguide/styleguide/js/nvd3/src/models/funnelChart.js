
nv.models.funnelChart = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var funnel = nv.models.funnel()
    //, xAxis = nv.models.axis()
    , yAxis = nv.models.axis()
    , legend = nv.models.legend()
    , controls = nv.models.legend()
    ;

  var margin = {top: 30, right: 20, bottom: 20, left: 60}
    , width = null
    , height = null
    , color = nv.utils.defaultColor()
    , showControls = false
    , showLegend = true
    , showTitle = false
    , reduceXTicks = false // if false a tick will show for every data point
    , rotateLabels = 0
    , tooltips = true
    , tooltip = function(key, x, y, e, graph) {
        return '<p>Stage: <b>' + key + '</b></p>' +
               '<p>Amount: <b>$' +  parseInt(y) + 'K</b></p>' +
               '<p>Percent: <b>' +  x + '%</b></p>'
      }
    , x //can be accessed via chart.xScale()
    , y //can be accessed via chart.yScale()
    , noData = "No Data Available."
    , dispatch = d3.dispatch('tooltipShow', 'tooltipHide')
    ;

  funnel
    .stacked(true)
    ;
  // xAxis
  //   .orient('bottom')
  //   .tickPadding(5)
  //   .highlightZero(false)
  //   .showMaxMin(false)
  //   .tickFormat(function(d) { return d })
  //   ;
  yAxis
    .orient('left')
    .tickFormat(d3.format(',.1f'))
    ;

  //============================================================


  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var showTooltip = function(e, offsetElement, properties) {
    var left = e.pos[0] + ( offsetElement.offsetLeft || 0 ),
        top = e.pos[1] + ( offsetElement.offsetTop || 0),
        x = (e.point.y * 100 / properties.total).toFixed(1)
        y = ( yAxis ).tickFormat()( funnel.y()(e.point, e.pointIndex) ),

        content = tooltip(e.series.key, x, y, e, chart);

    nv.tooltip.show([left, top], content, e.value < 0 ? 'n' : 's', null, offsetElement);
  };

  //============================================================


  function chart(selection) {

    selection.each(function(chartData) {

      var properties = chartData.properties
        , data = chartData.data;

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


      //------------------------------------------------------------
      // Setup Scales

      x = funnel.xScale();
      //y = funnel.yScale(); //see below

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-multiBarWithLegend').data([data]);
      var gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-multiBarWithLegend').append('g');
      var g = wrap.select('g');

      // gEnter.append('g').attr('class', 'nv-x nv-axis');
      gEnter.append('g').attr('class', 'nv-y nv-axis');
      gEnter.append('g').attr('class', 'nv-barsWrap');
      gEnter.append('g').attr('class', 'nv-legendWrap');
      gEnter.append('g').attr('class', 'nv-controlsWrap');

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Title & Legend

      var titleHeight = 0
        , legendHeight = 0;

      if (showLegend) 
      {
        legend.width(availableWidth);

        g.select('.nv-legendWrap')
            .datum(data)
            .call(legend);
        
        legendHeight = legend.height();

        if ( margin.top !== legendHeight + titleHeight ) {
          margin.top = legendHeight + titleHeight;
          availableHeight = (height || parseInt(container.style('height')) || 400)
                             - margin.top - margin.bottom;
        }

        g.select('.nv-legendWrap')
            .attr('transform', 'translate(0,' + (-margin.top) +')');
      }

      if (showTitle && properties.title ) 
      {
        gEnter.append('g').attr('class', 'nv-titleWrap');

        g.select('.nv-title').remove();

        g.select('.nv-titleWrap')
          .append('text')
            .attr('class', 'nv-title')
            .attr('x', 0)
            .attr('y', 0 )
            .attr('text-anchor', 'start')
            .text(properties.title)
            .attr('stroke', 'none')
            .attr('fill', 'black')
          ;

        titleHeight = parseInt( g.select('.nv-title').style('height') ) +  
          parseInt( g.select('.nv-title').style('margin-top') ) +  
          parseInt( g.select('.nv-title').style('margin-bottom') );

        if ( margin.top !== titleHeight + legendHeight ) 
        {
          margin.top = titleHeight + legendHeight;
          availableHeight = (height || parseInt(container.style('height')) || 400)
                             - margin.top - margin.bottom;
        }

        g.select('.nv-titleWrap')
            .attr('transform', 'translate(0,' + (-margin.top+parseInt( g.select('.nv-title').style('height') )) +')');
      }

      //------------------------------------------------------------


      //------------------------------------------------------------

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');


      //------------------------------------------------------------
      // Main Chart Component(s)

      funnel
        .width(availableWidth)
        .height(availableHeight)
        .color(data.map(function(d,i) {
          return d.color || color(d, i);
        }).filter(function(d,i) { return !data[i].disabled }));


      var funnelWrap = g.select('.nv-barsWrap')
          .datum(data.filter(function(d) { return !d.disabled }))

      d3.transition(funnelWrap).call(funnel);

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup Scales (again, not sure why it has to be here and not above?)

      var series1 = [{x:0,y:0}];
      var series2 = data.filter(
          function(d) { 
            return !d.disabled
          }
        ).map(
          function(d) {
            return d.values.map(
              function(d,i) {
                return { x: d.x, y: d.y0+d.y }
              }
            )
          }
      );

      // remap and flatten the data for use in calculating the scales' domains
      var minmax = d3.extent( d3.merge( series1.concat(series2) ), function(d) { return d.y } );
      var aTicks = d3.merge( series1.concat(series2) ).map( function(d) { return d.y } );

      y = d3.scale.linear().domain(minmax).range([availableHeight,0]);

      yScale = d3.scale.quantile()
                 .domain(aTicks)
                 .range(aTicks.map( function(d){ return y(d) } ));

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup Axes

      var w = availableHeight/1.1
        , r = ( ( (w/8) - w ) / 2 ) / availableHeight
        , c = availableWidth/2
        ;

      var yAxis = d3.svg.axis().scale(yScale)
                    .orient('left')
                    .tickSize( -availableWidth/2, 0)
                    .tickValues(aTicks)
                    .tickFormat( function(d,i) {
                      return '$' + d + 'K';
                    })
                    ;

      d3.transition(g.select('.nv-y.nv-axis')).call(yAxis);

      d3.transition(g.selectAll('.nv-y.nv-axis .tick')).attr('x2', function(d,i){ return c + ( r * y(aTicks[i]) ) + w/2 + 40 } );

      //------------------------------------------------------------


      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      legend.dispatch.on('legendClick', function(d,i) {
        d.disabled = !d.disabled;

        if (!data.filter(function(d) { return !d.disabled }).length) {
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
            funnel.stacked(false);
            break;
          case 'Stacked':
            funnel.stacked(true);
            break;
        }

        selection.transition().call(chart);
      });

      dispatch.on('tooltipShow', function(e) { 
        if (tooltips) showTooltip(e, that.parentNode, properties) 
      });

      //============================================================


      chart.update = function() { selection.transition().call(chart) };
      chart.container = this; 

    });

    return chart;
  }


  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  funnel.dispatch.on('elementMouseover.tooltip2', function(e) {
    e.pos = [e.pos[0] +  margin.left, e.pos[1] + margin.top];
    dispatch.tooltipShow(e);
  });

  funnel.dispatch.on('elementMouseout.tooltip', function(e) {
    dispatch.tooltipHide(e);
  });
  dispatch.on('tooltipHide', function() {
    if (tooltips) nv.tooltip.cleanup();
  });

  //============================================================


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.funnel = funnel;
  chart.legend = legend;
  // chart.xAxis = xAxis;
  chart.yAxis = yAxis;

  d3.rebind(chart, funnel, 'x', 'y', 'xDomain', 'yDomain', 'forceX', 'forceY', 'clipEdge', 'id', 'stacked', 'delay');

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

  chart.showTitle = function(_) {
    if (!arguments.length) return showTitle;
    showTitle = _;
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

  chart.tooltip = function(_) {
    if (!arguments.length) return tooltip;
    tooltip = _;
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

  //============================================================


  return chart;
}
