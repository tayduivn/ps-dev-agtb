
nv.models.lineChart = function () {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 10, right: 20, bottom: 10, left: 10}
    , width = null
    , height = null
    , showTitle = false
    , showControls = false
    , showLegend = true
    , tooltip = null
    , tooltips = true
    , tooltipContent = function (key, x, y, e, graph) {
        return '<h3>' + key + '</h3>' +
               '<p>' +  y + ' on ' + x + '</p>';
      }
    , x
    , y
    , state = {}
    , noData = 'No Data Available.'
    , dispatch = d3.dispatch('tooltipShow', 'tooltipHide', 'tooltipMove', 'stateChange', 'changeState')
    , controlWidth = function (w) { return showControls ? w * 0.3 : 0; }
    ;

  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var lines = nv.models.line()
    , xAxis = nv.models.axis()
        .orient('bottom')
        .tickPadding(7)
        .highlightZero(false)
        .showMaxMin(false)
        .tickFormat(function (d) { return d; })
    , yAxis = nv.models.axis()
        .orient('left')
        .tickPadding(4)
        .tickFormat(d3.format(',.1f'))
    , legend = nv.models.legend()
    , controls = nv.models.legend()
    ;

  var showTooltip = function (e, offsetElement) {
    var left = e.pos[0]
      , top = e.pos[1]
      , x = xAxis.tickFormat()(lines.x()(e.point, e.pointIndex))
      , y = yAxis.tickFormat()(lines.y()(e.point, e.pointIndex))
      , content = tooltipContent(e.series.key, x, y, e, chart);
    tooltip = nv.tooltip.show([left, top], content, null, null, offsetElement);
  };

  //============================================================

  function chart(selection) {

    selection.each(function (chartData) {

      var properties = chartData.properties
        , data = chartData.data;

      var container = d3.select(this)
        , that = this;

      var availableWidth = (width || parseInt(container.style('width'), 10) || 960) - margin.left - margin.right
        , availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom;

      var innerWidth = availableWidth
        , innerHeight = availableHeight
        , innerMargin = {top: 0, right: 0, bottom: 0, left: 0};

      chart.update = function () { container.transition().duration(chart.delay()).call(chart); };
      chart.container = this;

      //set state.disabled
      state.disabled = data.map(function (d) { return !!d.disabled; });

      //------------------------------------------------------------
      // Display No Data message if there's nothing to show.

      if (!data || !data.length || !data.filter(function (d) { return d.values.length; }).length) {
        var noDataText = container.selectAll('.nv-noData').data([noData]);

        noDataText.enter().append('text')
          .attr('class', 'nvd3 nv-noData')
          .attr('dy', '-.7em')
          .style('text-anchor', 'middle');

        noDataText
          .attr('x', margin.left + availableWidth / 2)
          .attr('y', margin.top + availableHeight / 2)
          .text(function (d) { return d; });

        return chart;
      } else {
        container.selectAll('.nv-noData').remove();
      }

      //------------------------------------------------------------
      // Setup Scales

      x = lines.xScale();
      y = lines.yScale();
      xAxis
        .scale(x);
      yAxis
        .scale(y);

      //------------------------------------------------------------

      //add series index to each data point for reference
      data = data.map(function (d, i) {
        d.series = i;
        return d;
      });

      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-lineChart').data([data]);
      var gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-lineChart').append('g');
      var g = wrap.select('g');

      gEnter.append('g').attr('class', 'nv-titleWrap');

      gEnter.append('g').attr('class', 'nv-x nv-axis');
      gEnter.append('g').attr('class', 'nv-y nv-axis');
      gEnter.append('g').attr('class', 'nv-linesWrap');

      gEnter.append('g').attr('class', 'nv-controlsWrap');
      gEnter.append('g').attr('class', 'nv-legendWrap');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------
      // Title & Legend & Controls

      var titleHeight = 0
        , controlsHeight = 0
        , legendHeight = 0;

      if (showTitle && properties.title) {
        g .select('.nv-title').remove();

        g .select('.nv-titleWrap')
          .append('text')
            .attr('class', 'nv-title')
            .attr('x', 0)
            .attr('y', 0)
            .attr('text-anchor', 'start')
            .text(properties.title)
            .attr('stroke', 'none')
            .attr('fill', 'black')
          ;

        titleHeight = parseInt(g.select('.nv-title').node().getBBox().height / 1.15, 10) +
          parseInt(g.select('.nv-title').style('margin-top'), 10) +
          parseInt(g.select('.nv-title').style('margin-bottom'), 10);

        g .select('.nv-title')
            .attr('dy', '.71em');
      }

      var controlsData = [
        { key: 'Linear', disabled: lines.interpolate() !== 'linear' },
        { key: 'Basis', disabled: lines.interpolate() !== 'basis' },
        { key: 'Monotone', disabled: lines.interpolate() !== 'monotone' },
        { key: 'Cardinal', disabled: lines.interpolate() !== 'cardinal' },
        { key: 'Line', disabled: lines.isArea()() === true },
        { key: 'Area', disabled: lines.isArea()() === false }
      ];

      if (showControls) {
        controls
          .id('controls_' + chart.id())
          .width(controlWidth(availableWidth))
          .height(availableHeight - titleHeight)
          .align('left')
          .strings({close: 'close', type: 'controls'})
          .color(['#444']);

        g .select('.nv-controlsWrap')
          .datum(controlsData)
          .attr('transform', 'translate(0,' + titleHeight + ')')
          .call(controls);

        controlsHeight = controls.height();
      }

      if (showLegend) {
        legend
          .id('legend_' + chart.id())
          .width(availableWidth - controlWidth(availableWidth))
          .height(availableHeight - titleHeight);

        g .select('.nv-legendWrap')
          .datum(data)
          .attr('transform', 'translate(' + controlWidth(availableWidth) + ',' + titleHeight + ')')
          .call(legend);

        legendHeight = legend.height();
      }

      //------------------------------------------------------------
      // Recalc inner margins

      innerMargin.top = titleHeight + Math.max(legendHeight,controlsHeight) + 4;
      innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;

      //------------------------------------------------------------
      // Main Chart Component(s)

      var linesWrap = g.select('.nv-linesWrap')
            .datum(data.filter(function (d) { return !d.disabled; }));

      lines
        .width(innerWidth)
        .height(innerHeight);

      linesWrap
          .call(lines);

      //------------------------------------------------------------
      // Setup Axes

      //------------------------------------------------------------
      // X-Axis

      g .select('.nv-x.nv-axis')
          .call(xAxis);

      //innerMargin.right = xAxis.maxTextWidth() / 2;
      innerMargin[xAxis.orient()] += xAxis.height();
      innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;

      //------------------------------------------------------------
      // Y-Axis

      g .select('.nv-y.nv-axis')
          .call(yAxis);

      innerMargin[yAxis.orient()] += yAxis.width();
      innerWidth = availableWidth - innerMargin.left - innerMargin.right;

      //------------------------------------------------------------
      // Main Chart Components
      // Recall to set final size

      lines
        .width(innerWidth)
        .height(innerHeight);

      linesWrap
        .attr('transform', 'translate(' + innerMargin.left + ',' + innerMargin.top + ')')
        .transition().duration(chart.delay())
          .call(lines);

      xAxis
        .ticks(innerWidth / 100)
        .tickSize(-innerHeight, 0);

      g .select('.nv-x.nv-axis')
        .attr('transform', 'translate(' + innerMargin.left + ',' + (xAxis.orient() === 'bottom' ? innerHeight + innerMargin.top : innerMargin.top) + ')')
        .transition()
          .call(xAxis);

      yAxis
        .ticks(innerHeight / 36)
        .tickSize(-innerWidth, 0);

      g .select('.nv-y.nv-axis')
        .attr('transform', 'translate(' + (yAxis.orient() === 'left' ? innerMargin.left : innerMargin.left + innerWidth) + ',' + innerMargin.top + ')')
        .transition()
          .call(yAxis);

      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      legend.dispatch.on('legendClick', function (d, i) {
        d.disabled = !d.disabled;
        if (!data.filter(function (d) { return !d.disabled; }).length) {
          data.map(function (d) {
            d.disabled = false;
            wrap.selectAll('.nv-series').classed('disabled', false);
            return d;
          });
        }
        state.disabled = data.map(function (d) { return !!d.disabled; });
        dispatch.stateChange(state);
        container.transition().duration(chart.delay()).call(chart);
      });

      controls.dispatch.on('legendClick', function (d, i) {
        if (!d.disabled) { return; }
        controlsData = controlsData.map(function (s) {
          s.disabled = true;
          return s;
        });
        d.disabled = false;

        switch (d.key) {
          case 'Basis':
            lines.interpolate('basis');
            break;
          case 'Linear':
            lines.interpolate('linear');
            break;
          case 'Monotone':
            lines.interpolate('monotone');
            break;
          case 'Cardinal':
            lines.interpolate('cardinal');
            break;
          case 'Line':
            lines.isArea(false);
            break;
          case 'Area':
            lines.isArea(true);
            break;
        }

        container.transition().duration(chart.delay()).call(chart);
      });

      dispatch.on('tooltipShow', function (e) {
        if (tooltips) {
          showTooltip(e, that.parentNode);
        }
      });

      // Update chart from a state object passed to event handler
      dispatch.on('changeState', function (e) {
        if (typeof e.disabled !== 'undefined') {
          data.forEach(function (series,i) {
            series.disabled = e.disabled[i];
          });
          state.disabled = e.disabled;
        }
        container.transition().duration(chart.delay()).call(chart);
      });

    });

    return chart;
  }

  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  lines.dispatch.on('elementMouseover.tooltip', function (e) {
    e.pos = [e.pos[0] + margin.left, e.pos[1] + margin.top];
    dispatch.tooltipShow(e);
  });

  lines.dispatch.on('elementMouseout.tooltip', function (e) {
    dispatch.tooltipHide(e);
  });
  dispatch.on('tooltipHide', function () {
    if (tooltips) {
      nv.tooltip.cleanup();
    }
  });

  lines.dispatch.on('elementMousemove.tooltip', function (e) {
    dispatch.tooltipMove(e);
  });
  dispatch.on('tooltipMove', function (e) {
    if (tooltip) {
      nv.tooltip.position(tooltip, e.pos, 's');
    }
  });


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.lines = lines;
  chart.legend = legend;
  chart.controls = controls;
  chart.xAxis = xAxis;
  chart.yAxis = yAxis;

  d3.rebind(chart, lines, 'defined', 'isArea', 'x', 'y', 'size', 'xScale', 'yScale', 'xDomain', 'yDomain', 'forceX', 'forceY', 'interactive', 'clipEdge', 'id', 'delay', 'clipVoronoi', 'id', 'interpolate', 'color', 'fill', 'classes', 'gradient');
  d3.rebind(chart, xAxis, 'rotateLabels', 'reduceXTicks');

  chart.colorData = function (_) {
    var colors = function (d,i) { return nv.utils.defaultColor()(d, d.series); },
        classes = function (d,i) { return 'nv-group nv-series-' + i; },
        type = arguments[0],
        params = arguments[1] || {};

    switch (type) {
      case 'graduated':
        var c1 = params.c1
          , c2 = params.c2
          , l = params.l;
        colors = function (d,i) { return d3.interpolateHsl( d3.rgb(c1), d3.rgb(c2) )(d.series/l); };
        break;
      case 'class':
        colors = function () { return 'inherit'; };
        classes = function (d,i) {
          var iClass = (d.series * (params.step || 1)) % 20;
          return 'nv-group nv-series-' + i + ' ' + (d.classes || 'nv-fill' + (iClass>9?'':'0') + iClass + ' nv-stroke' + d.series);
        };
        break;
    }

    var fill = (!params.gradient) ? colors : function (d,i) {
      var p = {orientation: params.orientation || 'horizontal', position: params.position || 'base'};
      return lines.gradient(d,d.series,p);
    };

    lines.color(colors);
    lines.fill(fill);
    lines.classes(classes);

    legend.color(colors);
    legend.classes(classes);

    return chart;
  };

  chart.margin = function (_) {
    if (!arguments.length) { return margin; }
    margin.top    = typeof _.top    != 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  != 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom != 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   != 'undefined' ? _.left   : margin.left;
    return chart;
  };

  chart.width = function (_) {
    if (!arguments.length) { return width; }
    width = _;
    return chart;
  };

  chart.height = function (_) {
    if (!arguments.length) { return height; }
    height = _;
    return chart;
  };

  chart.showTitle = function (_) {
    if (!arguments.length) { return showTitle; }
    showTitle = _;
    return chart;
  };

  chart.showControls = function (_) {
    if (!arguments.length) { return showControls; }
    showControls = _;
    return chart;
  };

  chart.showLegend = function (_) {
    if (!arguments.length) { return showLegend; }
    showLegend = _;
    return chart;
  };

  chart.tooltip = function (_) {
    if (!arguments.length) { return tooltip; }
    tooltip = _;
    return chart;
  };

  chart.tooltips = function (_) {
    if (!arguments.length) { return tooltips; }
    tooltips = _;
    return chart;
  };

  chart.tooltipContent = function (_) {
    if (!arguments.length) { return tooltipContent; }
    tooltipContent = _;
    return chart;
  };

  chart.state = function (_) {
    if (!arguments.length) { return state; }
    state = _;
    return chart;
  };

  chart.noData = function (_) {
    if (!arguments.length) { return noData; }
    noData = _;
    return chart;
  };

  //============================================================

  return chart;
};
