
nv.models.funnelChart = function () {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 10, right: 10, bottom: 10, left: 10}
    , width = null
    , height = null
    , showTitle = false
    , showControls = false
    , showLegend = true
    , tooltip = null
    , tooltips = true
    , tooltipContent = function (key, x, y, e, graph) {
        return '<h3>' + key + " - " + x + '</h3>' +
               '<p>' +  y + '</p>';
      }
    , x
    , y
    , noData = 'No Data Available.'
    , dispatch = d3.dispatch('tooltipShow', 'tooltipHide', 'tooltipMove')
    , controlWidth = function (w) { return showControls ? w * 0.3 : 0; }
    ;

  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var funnel = nv.models.funnel()
    , yAxis = nv.models.axis()
        .orient('left')
        .tickFormat(function (d) { return ''; })
    , legend = nv.models.legend()
    ;

    var showTooltip = function (e, offsetElement, properties) {
        var xVal = 0;
        // defense against the dark divide-by-zero arts
        if(properties.total > 0) {
            xVal = (e.point.value * 100 / properties.total).toFixed(1);
        }

        var left = e.pos[0]
            , top = e.pos[1]
            , x = xVal
            , y = e.point.label
            , content = tooltipContent(e.series.key, x, y, e, chart);
        tooltip = nv.tooltip.show([left, top], content, e.value < 0 ? 'n' : 's', null, offsetElement);
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

      x = funnel.xScale();
      //y = funnel.yScale(); //see below

      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-funnelChart').data([data]);
      var gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-funnelChart').append('g');
      var g = wrap.select('g');

      gEnter.append('g').attr('class', 'nv-titleWrap');

      gEnter.append('g').attr('class', 'nv-y nv-axis');
      gEnter.append('g').attr('class', 'nv-funnelWrap');

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

      if (showLegend) {
        legend
          .id('legend_' + chart.id())
          .width(availableWidth - controlWidth(availableWidth))
          .height(availableHeight - titleHeight)
          .align('center');

        g .select('.nv-legendWrap')
          .datum(data)
          .attr('transform', 'translate(' + controlWidth(availableWidth) + ',' + titleHeight + ')')
          .call(legend);

        legendHeight = legend.height();
      }

      //------------------------------------------------------------
      // Recalc inner margins

      innerMargin.top = titleHeight + Math.max(legendHeight,controlsHeight) + 8;
      innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;

      //------------------------------------------------------------
      // Main Chart Component(s)

      var funnelWrap = g.select('.nv-funnelWrap')
            .datum(data.filter(function (d) { return !d.disabled; }));

      funnel
        .width(innerWidth)
        .height(innerHeight);

      funnelWrap
          .call(funnel);

      //------------------------------------------------------------
      // Setup Scales (again, not sure why it has to be here and not above?)

      var series1 = [{x:0,y:0}];
      var series2 = data.filter(function (d) {
              return !d.disabled;
            })
            .map(function (d) {
              return d.values.map(function (d,i) {
                return { x: d.x, y: d.y0+d.y };
              });
            });
      var tickData = d3.merge( series1.concat(series2) );

      // remap and flatten the data for use in calculating the scales' domains
      var minmax = d3.extent(tickData, function (d) { return d.y; });
      var aTicks = d3.merge(tickData).map(function (d) { return d.y; });

      y = d3.scale.linear().domain(minmax).range([innerHeight,0]);

      yScale = d3.scale.quantile()
                 .domain(aTicks)
                 .range(aTicks.map(function (d){ return y(d); }));

      //------------------------------------------------------------
      // Main Chart Components
      // Recall to set final size

      funnel
        .width(innerWidth)
        .height(innerHeight);

      funnelWrap
        .attr('transform', 'translate(' + innerMargin.left + ',' + innerMargin.top + ')')
        .transition().duration(chart.delay())
          .call(funnel);

      yAxis
        .tickSize(-innerWidth, 0)
        .scale(yScale)
        .tickValues(aTicks);

      g .select('.nv-y.nv-axis')
        .attr('transform', 'translate(' + (yAxis.orient() === 'left' ? innerMargin.left : innerWidth) + ',' + innerMargin.top + ')')
        .transition().duration(chart.delay())
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
        container.transition().duration(chart.delay()).call(chart);
      });

      dispatch.on('tooltipShow', function (e) {
        if (tooltips) {
          showTooltip(e, that.parentNode, properties);
        }
      });

    });

    return chart;
  }

  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  funnel.dispatch.on('elementMouseover.tooltip', function (e) {
    dispatch.tooltipShow(e);
  });

  funnel.dispatch.on('elementMouseout.tooltip', function (e) {
    dispatch.tooltipHide(e);
  });
  dispatch.on('tooltipHide', function () {
    if (tooltips) {
      nv.tooltip.cleanup();
    }
  });

  funnel.dispatch.on('elementMousemove', function (e) {
    dispatch.tooltipMove(e);
  });
  dispatch.on('tooltipMove', function (e) {
    if (tooltip) {
      nv.tooltip.position(tooltip, e.pos);
    }
  });


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.funnel = funnel;
  chart.legend = legend;
  chart.yAxis = yAxis;

  d3.rebind(chart, funnel, 'x', 'y', 'xDomain', 'yDomain', 'forceX', 'forceY', 'clipEdge', 'id', 'delay', 'fmtValueLabel', 'color', 'fill', 'classes', 'gradient');

  chart.colorData = function (_) {
    var colors = function (d,i) { return nv.utils.defaultColor()(d,i); },
        classes = function (d,i) { return 'nv-group nv-series-' + i; },
        type = arguments[0],
        params = arguments[1] || {};

    switch (type) {
      case 'graduated':
        var c1 = params.c1
          , c2 = params.c2
          , l = params.l;
        colors = function (d,i) { return d3.interpolateHsl( d3.rgb(c1), d3.rgb(c2) )(i/l); };
        break;
      case 'class':
        colors = function () { return 'inherit'; };
        classes = function (d,i) {
          var iClass = (i*(params.step || 1)) % 20;
          return 'nv-group nv-series-' + i + ' ' + (d.classes || 'nv-fill' + (iClass>9?'':'0') + iClass);
        };
        break;
    }

    var fill = (!params.gradient) ? colors : function (d,i) {
      var p = {orientation: params.orientation || 'vertical', position: params.position || 'middle'};
      return funnel.gradient(d,i,p);
    };

    funnel.color(colors);
    funnel.fill(fill);
    funnel.classes(classes);

    legend.color(colors);
    legend.classes(classes);

    return chart;
  };

  chart.x = function (_) {
    if (!arguments.length) { return getX; }
    getX = _;
    lines.x(_);
    funnelWrap.x(_);
    return chart;
  };

  chart.y = function (_) {
    if (!arguments.length) { return getY; }
    getY = _;
    lines.y(_);
    funnel.y(_);
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

  chart.noData = function (_) {
    if (!arguments.length) { return noData; }
    noData = _;
    return chart;
  };

  chart.colorFill = function (_) {
    return chart;
  };

  //============================================================

  return chart;
};
