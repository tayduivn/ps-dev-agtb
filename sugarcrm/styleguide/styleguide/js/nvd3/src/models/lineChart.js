
nv.models.lineChart = function () {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 30, right: 20, bottom: 50, left: 60}
    , width = null
    , height = null
    , showControls = false
    , showLegend = true
    , showTitle = false
    , tooltips = true
    , tooltip = function (key, x, y, e, graph) {
        return '<h3>' + key + '</h3>' +
               '<p>' +  y + ' on ' + x + '</p>';
      }
    , x
    , y
    , noData = "No Data Available."
    , dispatch = d3.dispatch('tooltipShow', 'tooltipHide')
    ;

  //============================================================


  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var lines = nv.models.line()
    , xAxis = nv.models.axis().orient('bottom').tickPadding(5)
    , yAxis = nv.models.axis().orient('left')
    , legend = nv.models.legend()
    , controls = nv.models.legend()
    ;

  var showTooltip = function (e, offsetElement) {

    // New addition to calculate position if SVG is scaled with viewBox, may move TODO: consider implementing everywhere else
    if (offsetElement) {
      var svg = d3.select(offsetElement).select('svg');
      var viewBox = svg.attr('viewBox');
      if (viewBox) {
        viewBox = viewBox.split(' ');
        var ratio = parseInt(svg.style('width'), 10) / viewBox[2];
        e.pos[0] = e.pos[0] * ratio;
        e.pos[1] = e.pos[1] * ratio;
      }
    }

    var left = e.pos[0] + ( offsetElement.offsetLeft || 0 ),
        top = e.pos[1] + ( offsetElement.offsetTop || 0),
        x = xAxis.tickFormat()(lines.x()(e.point, e.pointIndex)),
        y = yAxis.tickFormat()(lines.y()(e.point, e.pointIndex)),
        content = tooltip(e.series.key, x, y, e, chart);

    nv.tooltip.show([left, top], content, null, null, offsetElement);
  };

  //============================================================


  function chart(selection) {

    selection.each(function (chartData) {

      var properties = chartData.properties
        , data = chartData.data;

      var container = d3.select(this),
          that = this;

      var availableWidth = (width  || parseInt(container.style('width'), 10) || 960) - margin.left - margin.right,
          availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom;

      //------------------------------------------------------------
      // Display noData message if there's nothing to show.

      if (!data || !data.length || !data.filter(function(d) { return d.values.length }).length) {
        var noDataText = container.selectAll('.nv-noData').data([noData]);

        noDataText.enter().append('text')
          .attr('class', 'nvd3 nv-noData')
          .attr('dy', '-.7em')
          .style('text-anchor', 'middle');

        noDataText
          .attr('x', margin.left + availableWidth / 2)
          .attr('y', margin.top + availableHeight / 2)
          .text(function(d) { return d });

        return chart;
      } else {
        container.selectAll('.nv-noData').remove();
      }

      //------------------------------------------------------------

      //add series index to each data point for reference
      data = data.map(function (d, i) {
        d.series = i;
        return d;
      });


      //------------------------------------------------------------
      // Setup Scales

      x = lines.xScale();
      y = lines.yScale();

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-lineChart').data([data]);
      var gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-lineChart').append('g');
      var g = wrap.select('g');

      gEnter.append('g').attr('class', 'nv-x nv-axis');
      gEnter.append('g').attr('class', 'nv-y nv-axis');
      gEnter.append('g').attr('class', 'nv-linesWrap');

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Title & Legend

      var titleHeight = 0
        , legendHeight = 0;

      if (showLegend)
      {
        gEnter.append('g').attr('class', 'nv-legendWrap');

        legend.width(availableWidth*(showControls?0.7:1));

        g.select('.nv-legendWrap')
          .datum(data)
          .call(legend);

        legendHeight = legend.height();

        if ( margin.top !== legendHeight + titleHeight ) {
          margin.top = legendHeight + titleHeight;
          availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom;
        }

        g.select('.nv-legendWrap')
            .attr('transform', 'translate('+ (availableWidth*(showControls?0.3:0)) +','+ (-margin.top) +')');
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

        titleHeight = parseInt(g.select('.nv-title').node().getBBox().height, 10) +
          parseInt(g.select('.nv-title').style('margin-top'), 10) +
          parseInt(g.select('.nv-title').style('margin-bottom'), 10);

        if ( margin.top !== titleHeight + legendHeight )
        {
          margin.top = titleHeight + legendHeight;
          availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom;
        }

        g.select('.nv-titleWrap')
            .attr('transform', 'translate(0,' + (-margin.top+parseInt(g.select('.nv-title').node().getBBox().height, 10)) +')');
      }

      //------------------------------------------------------------
      // Controls

      var controlsData = [
        { key: 'Linear', disabled: lines.interpolate() !== 'linear' },
        { key: 'Basis', disabled: lines.interpolate() !== 'basis' },
        { key: 'Monotone', disabled: lines.interpolate() !== 'monotone' },
        { key: 'Cardinal', disabled: lines.interpolate() !== 'cardinal' },
        { key: 'Line', disabled: lines.isArea()() === true },
        { key: 'Area', disabled: lines.isArea()() === false }
      ];

      if (showControls)
      {
        gEnter.append('g').attr('class', 'nv-controlsWrap');

        controls.width(availableWidth*0.3).color(['#444']);

        g.select('.nv-controlsWrap')
            .datum(controlsData)
            .attr('transform', 'translate(0,' + (-margin.top+titleHeight) +')')
            .call(controls);
      }

      //------------------------------------------------------------

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');


      //------------------------------------------------------------
      // Main Chart Component(s)

      lines
        .width(availableWidth)
        .height(availableHeight)
      ;


      var linesWrap = g.select('.nv-linesWrap')
          .datum(data.filter(function (d) { return !d.disabled; }));

      d3.transition(linesWrap).call(lines);

      //------------------------------------------------------------
      // Setup Axes

      xAxis
        .scale(x)
        .ticks( availableWidth / 100 )
        .tickSize(-availableHeight, 0);

      g.select('.nv-x.nv-axis')
          .attr('transform', 'translate(0,'+ y.range()[0] +')');
      d3.transition(g.select('.nv-x.nv-axis'))
          .call(xAxis);

      yAxis
        .scale(y)
        .ticks( availableHeight / 36)
        .tickSize(-availableWidth, 0);

      d3.transition(g.select('.nv-y.nv-axis'))
          .call(yAxis);

      //------------------------------------------------------------


      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      legend.dispatch.on('legendClick', function (d,i) {
        d.disabled = !d.disabled;

        if (!data.filter(function (d) { return !d.disabled; }).length) {
          data.map(function (d) {
            d.disabled = false;
            wrap.selectAll('.nv-series').classed('disabled', false);
            return d;
          });
        }

        selection.transition().call(chart);
      });

/*
      legend.dispatch.on('legendMouseover', function (d, i) {
        d.hover = true;
        selection.transition().call(chart)
      });

      legend.dispatch.on('legendMouseout', function (d, i) {
        d.hover = false;
        selection.transition().call(chart)
      });
*/

      controls.dispatch.on('legendClick', function (d,i) {
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

        selection.transition().call(chart);
      });

      dispatch.on('tooltipShow', function (e) {
        if (tooltips) {
          showTooltip(e, that.parentNode);
        }
      });

      //============================================================


      chart.update = function () { chart(selection); };
      chart.container = this;

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

  //============================================================


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.lines = lines;
  chart.legend = legend;
  chart.xAxis = xAxis;
  chart.yAxis = yAxis;

  d3.rebind(chart, lines, 'defined', 'isArea', 'x', 'y', 'size', 'xScale', 'yScale', 'xDomain', 'yDomain', 'forceX', 'forceY', 'interactive', 'clipEdge', 'clipVoronoi', 'id', 'interpolate', 'color', 'fill', 'classes', 'gradient');

  chart.colorData = function (_) {
    var colors = function (d,i) { return nv.utils.defaultColor()(d,d.series); },
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
          return 'nv-group nv-series-'+ i +' '+ (d.classes || 'nv-fill'+ (iClass>9?'':'0') + iClass +' nv-stroke'+ d.series);
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
    margin = _;
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

  chart.showTitle = function (_) {
    if (!arguments.length) { return showTitle; }
    showTitle = _;
    return chart;
  };

  chart.tooltips = function (_) {
    if (!arguments.length) { return tooltips; }
    tooltips = _;
    return chart;
  };

  chart.tooltipContent = function (_) {
    if (!arguments.length) { return tooltip; }
    tooltip = _;
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
