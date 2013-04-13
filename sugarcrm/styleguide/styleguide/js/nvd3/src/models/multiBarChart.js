
nv.models.multiBarChart = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 30, right: 20, bottom: 20, left: 40}//margin = {top: 30, right: 20, bottom: 50, left: 60}
    , width = null
    , height = null
    , getX = function(d) { return d.x; }
    , getY = function(d) { return d.y; }
    , showControls = false
    , showLegend = true
    , showTitle = false
    , reduceXTicks = false // if false a tick will show for every data point
    , reduceYTicks = false // if false a tick will show for every data point
    , rotateLabels = 0
    //, rotateLabels = -15
    , tooltips = true
    , tooltip = function(key, x, y, e, graph) {
        return '<h3>' + key + " - " + x + '</h3>' +
               '<p>' +  y + '</p>';
      }
    //, x //can be accessed via chart.xScale()
    //, y //can be accessed via chart.yScale()
    , noData = "No Data Available."
    ;

  var multibar = nv.models.multiBar().stacked(true)
    , x = multibar.xScale()
    , y = multibar.yScale()
    , xAxis = nv.models.axis().scale(x).orient('bottom').tickPadding(5).tickFormat(function(d) { return d; })
    , yAxis = nv.models.axis().scale(y).orient('left').tickFormat(function(d) { return d + 'K'; })
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
        x = (e.point.y * 100 / groupTotals[e.pointIndex].t).toFixed(1),
        y = yAxis.tickFormat()( multibar.y()(e.point, e.pointIndex) ),
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


      var dataBars = data.filter(function(d) { return !d.disabled && d.type==='bar'; });

      var groupLabels = properties.labels;

      var groupTotals = properties.values;

      //TODO: try to remove x scale computation from this layer

      var series1 = data.filter(
            function(d) {
              return !d.disabled;
            }
          ).map(
            function(d) {
              return d.values.map(
                function(d,i) {
                  return { x: getX(d,i), y: getY(d,i) };
                }
              );
            }
        );

      x   .domain(d3.extent(d3.merge(series1), function(d) { return d.x; } ))
          .range([0, availableWidth]);

      //x   .domain(d3.extent(d3.merge(data.map(function(d) { return d.values })), getX ))
      //   .range([0, availableWidth]);

      y   .domain(d3.extent(d3.merge(series1), function(d) { return d.y; } ))
          .range([availableHeight, 0]);


      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-multiBarWithLegend').data([data]);
      var gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-multiBarWithLegend').append('g');
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
            .attr('y', 0)
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
            .attr('transform', 'translate(0,'+ ( -margin.top+parseInt(g.select('.nv-title').node().getBBox().height, 10) ) +')');
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

        controls.width(availableWidth*0.3).color(['#444']);

        g.select('.nv-controlsWrap')
            .datum(controlsData)
            .attr('transform', 'translate(0,'+ (-margin.top+titleHeight) +')')
            .call(controls);
      }

      //------------------------------------------------------------


      wrap.attr('transform', 'translate('+ margin.left +','+ margin.top +')');


      //------------------------------------------------------------
      // Main Chart Component(s)

      multibar
        .width(availableWidth)
        .height(availableHeight);

      var barsWrap = g.select('.nv-barsWrap')
          .datum(dataBars.length ? dataBars : [{values:[]}]);

      d3.transition(barsWrap).call(multibar);


      //------------------------------------------------------------
      // Setup Axes

      xAxis
        .ticks( availableWidth / 100 )
        .tickSize(-availableHeight, 0)
        //.tickFormat(function(d) { return d })
        .tickFormat(function(d,i) {
          return groupLabels[i] ? groupLabels[i].l : 'asfd';
        });

      g.select('.nv-x.nv-axis')
          .attr('transform', 'translate(0,'+ y.range()[0] +')');
      d3.transition(g.select('.nv-x.nv-axis'))
          .call(xAxis);

      var xTicks = g.select('.nv-x.nv-axis > g').selectAll('g');

      xTicks
          .selectAll('line, text')
          .style('opacity', 1);

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
            .attr('transform', function(d,i,j) { return 'rotate('+ rotateLabels +' 0,0) translate(0,10)'; })
            .attr('text-transform', rotateLabels > 0 ? 'start' : 'end');
        // xTicks
        //     .selectAll('text')
        //     .attr('transform', 'translate(0,10)');
      }

      yAxis
        .ticks( availableHeight / 100 )
        .tickSize(-availableWidth, 0);

      d3.transition(g.select('.nv-y.nv-axis'))
          .style('opacity', dataBars.length ? 1 : 0)
          .call(yAxis);

      //------------------------------------------------------------


      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      legend.dispatch.on('legendClick', function(d,i) {
        d.disabled = !d.disabled;

        if (!data.filter(function(d) { return !d.disabled; }).length) {
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

      /*dispatch.on('tooltipShow', function(e) {
        if (tooltips) showTooltip(e, that.parentNode)
      });*/


      multibar.dispatch.on('elementMouseover.tooltip', function(e) {
        e.pos = [e.pos[0] +  margin.left, e.pos[1] + margin.top];
        dispatch.tooltipShow(e);
      });

      multibar.dispatch.on('elementMouseout.tooltip', function(e) {
        dispatch.tooltipHide(e);
      });

      // multibar.dispatch.on('elementClick', function(e) {
      //   //console.log(data.filter(function(d) { return !d.disabled }))
      //   barClick(data,e,selection);
      // });

      if (tooltips) {
        dispatch.on('tooltipShow', function(e) { showTooltip(e, that.parentNode, groupTotals); } ); // TODO: maybe merge with above?
      }
      if (tooltips) {
        dispatch.on('tooltipHide', nv.tooltip.cleanup);
      }

      //============================================================


      chart.update = function() { selection.transition().call(chart); };
      chart.container = this; // I need a reference to the container in order to have outside code check if the chart is visible or not

    });

    return chart;
  }


  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  /*multibar.dispatch.on('elementMouseover.tooltip2', function(e) {
    e.pos = [e.pos[0] +  margin.left, e.pos[1] + margin.top];
    dispatch.tooltipShow(e);
  });

  multibar.dispatch.on('elementMouseout.tooltip', function(e) {
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
  chart.multibar = multibar;
  chart.legend = legend;
  chart.xAxis = xAxis;
  chart.yAxis = yAxis;

  d3.rebind(chart, multibar, 'x', 'y', 'xDomain', 'yDomain', 'forceX', 'forceY', 'clipEdge', 'id', 'stacked', 'delay', 'color', 'fill', 'classes', 'gradient');

  //consider rebinding x and y as well

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
          var iClass = (i*(params.step || 1))%20;
          return 'nv-group nv-series-'+ i +' '+ ( d.classes || 'nv-fill'+ (iClass>9?'':'0') + iClass );
        };
        break;
    }

    var fill = (!params.gradient) ? colors : function (d,i) {
      var p = {orientation: params.orientation || 'vertical', position: params.position || 'middle'};
      return multibar.gradient(d,i,p);
    };

    multibar.color(colors);
    multibar.fill(fill);
    multibar.classes(classes);

    legend.color(colors);
    legend.classes(classes);

    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) return getX;
    getX = _;
    multibar.x(_);
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) return getY;
    getY = _;
    multibar.y(_);
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

  chart.reduceXTicks= function(_) {
    if (!arguments.length) return reduceXTicks;
    reduceXTicks = _;
    return chart;
  };

  chart.rotateLabels = function(_) {
    if (!arguments.length) return rotateLabels;
    rotateLabels = _;
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
