
nv.models.multiBarHorizontalChart = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 30, right: 20, bottom: 50, left: 60}
    , width = null
    , height = null
    , showControls = true
    , showLegend = true
    , showTitle = false
    , stacked = true
    , tooltips = true
    , tooltip = function(key, x, y, e, graph) { 
        return '<h3>' + key + " - " + x + '</h3>' +
               '<p>' +  y + '</p>'
      }
    , noData = "No Data Available."
    ;


  var multibar = nv.models.multiBarHorizontal().stacked(stacked)
    , x = multibar.xScale()
    , y = multibar.yScale()
    , xAxis = nv.models.axis().scale(x).orient('left').highlightZero(false).showMaxMin(false)
    , yAxis = nv.models.axis().scale(y).orient('bottom')
    , legend = nv.models.legend().height(30)
    , controls = nv.models.legend().height(30)
    , dispatch = d3.dispatch('tooltipShow', 'tooltipHide')
    ;

  //============================================================


  //============================================================
  // Private Variables
  //------------------------------------------------------------

  xAxis.tickFormat(function(d) { return d });
  yAxis.tickFormat(d3.format(',.1f'));

  var showTooltip = function(e, offsetElement) {
    var left = e.pos[0] + ( offsetElement.offsetLeft || 0 ),
        top = e.pos[1] + ( offsetElement.offsetTop || 0),
        x = xAxis.tickFormat()(multibar.x()(e.point, e.pointIndex)),
        y = yAxis.tickFormat()(multibar.y()(e.point, e.pointIndex)),
        content = tooltip(e.series.key, x, y, e, chart);

    nv.tooltip.show([left, top], content, e.value < 0 ? 'e' : 'w', null, offsetElement);
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
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-multiBarHorizontalChart').data([data]);
      var gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-multiBarHorizontalChart').append('g');
      var g = wrap.select('g');

      gEnter.append('g').attr('class', 'nv-x nv-axis');
      gEnter.append('g').attr('class', 'nv-y nv-axis');
      gEnter.append('g').attr('class', 'nv-barsWrap');

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Title & Legend

      var titleHeight = 0
        , legendHeight = 0;

      if (showLegend) 
      {
        gEnter.append('g').attr('class', 'nv-legendWrap');

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
      // Controls

      if (showControls) 
      {
        gEnter.append('g').attr('class', 'nv-controlsWrap');

        var controlsData = [
          { key: 'Grouped', disabled: multibar.stacked() },
          { key: 'Stacked', disabled: !multibar.stacked() }
        ];

        controls.width(180).color(['#444', '#444', '#444']);

        g.select('.nv-controlsWrap')
            .datum(controlsData)
            .attr('transform', 'translate(0,' + (-margin.top) +')')
            .call(controls);
      }

      //------------------------------------------------------------

      g.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');


      //------------------------------------------------------------
      // Main Chart Component(s)

      multibar
        .width(availableWidth)
        .height(availableHeight)

      var barsWrap = g.select('.nv-barsWrap')
          .datum(data.filter(function(d) { return !d.disabled }))


      d3.transition(barsWrap).call(multibar);

      //------------------------------------------------------------
      // Setup Axes

      xAxis
        .ticks( availableHeight / 24 )
        .tickSize(-availableWidth, 0);

      //d3.transition(g.select('.x.axis'))
      g.select('.nv-x.nv-axis').transition().duration(0)
          .call(xAxis);

      var xTicks = g.select('.nv-x.nv-axis').selectAll('g');

      xTicks
          .selectAll('line, text')
          .style('opacity', 1)

          /*
      //I think this was just leaft over from the multiBar chart this was built from.. commented to maek sure
      xTicks.filter(function(d,i) {
            return i % Math.ceil(data[0].values.length / (availableWidth / 100)) !== 0;
          })
          .selectAll('line, text')
          .style('opacity', 0)
          */

      yAxis
        .ticks( availableWidth / 100 )
        .tickSize( -availableHeight, 0);

      g.select('.nv-y.nv-axis')
          .attr('transform', 'translate(0,' + availableHeight + ')');
      d3.transition(g.select('.nv-y.nv-axis'))
      //g.select('.y.axis').transition().duration(0)
          .call(yAxis);

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
            multibar.stacked(false);
            break;
          case 'Stacked':
            multibar.stacked(true);
            break;
        }

        selection.transition().call(chart);
      });


      multibar.dispatch.on('elementMouseover.tooltip', function(e) {
        e.pos = [e.pos[0] +  margin.left, e.pos[1] + margin.top];
        dispatch.tooltipShow(e);
      });
      if (tooltips) dispatch.on('tooltipShow', function(e) { showTooltip(e, that.parentNode) } ); // TODO: maybe merge with above?

      multibar.dispatch.on('elementMouseout.tooltip', function(e) {
        dispatch.tooltipHide(e);
      });
      if (tooltips) dispatch.on('tooltipHide', nv.tooltip.cleanup);

      //============================================================


      //TODO: decide if this makes sense to add into all the models for ease of updating (updating without needing the selection)
      chart.update = function() { selection.transition().call(chart) };
      chart.container = this; // I need a reference to the container in order to have outside code check if the chart is visible or not

    });

    return chart;
  }


  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  //============================================================


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.multibar = multibar; // really just makign the accessible for multibar.dispatch, may rethink slightly
  chart.legend = legend;
  chart.xAxis = xAxis;
  chart.yAxis = yAxis;

  d3.rebind(chart, multibar, 'x', 'y', 'xDomain', 'yDomain', 'forceX', 'forceY', 'clipEdge', 'id', 'stacked', 'delay', 'showValues', 'valueFormat', 'color', 'gradient', 'useClass');

  chart.colorData = function(_) {
    if (arguments[0] === 'graduated')
    {
      var c1 = arguments[1].c1
        , c2 = arguments[1].c2
        , l = arguments[1].l;
      var color = function (d,i) { return d3.interpolateHsl( d3.rgb(c1), d3.rgb(c2) )(i/l) };
    }
    else if (_ === 'class')
    {
      chart.useClass(true);
      legend.useClass(true);
      var color = function (d,i) { return 'inherit' };
    }
    else
    {
      var color = nv.utils.defaultColor();
    }

    legend.color(color);
    multibar.color(color);

    return chart;
  };

  chart.colorFill = function(_) {
    if (_ === 'gradient')
    {
      var fill = function (d,i) { return chart.gradient()(d,i) };
    }
    else
    {
      var fill = chart.color();
    }

    multibar.fill(fill);

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
