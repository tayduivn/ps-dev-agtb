
nv.models.bubbleChart = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------
var format = d3.time.format("%Y-%m-%d");

  var margin = {top: 70, right: 20, bottom: 30, left: 90}
    , width = null
    , height = null
    , getX = function(d) { return d.x; }
    , getY = function(d) { return d.y; }
    , color = nv.utils.defaultColor()
    , showLegend = true
    , showTitle = false
    , reduceXTicks = true // if false a tick will show for every data point
    , reduceYTicks = false // if false a tick will show for every data point
    , rotateLabels = 0
    , tooltips = true
    , tooltip = function(key, x, y, e, graph) {
        return '<h3>' + key + '</h3>' +
               '<p>' +  y + ' on ' + x + '</p>';
      }
    , noData = "No Data Available."
    ;

  //============================================================


  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var bubbles = nv.models.bubble()
    , x = bubbles.xScale()
    , y = bubbles.yScale()
    , xAxis = nv.models.axis()
        .orient('bottom')
        .tickPadding(5)
        .highlightZero(false)
        .showMaxMin(false)
    , yAxis = nv.models.axis()
        .orient('left')
        .highlightZero(false)
        .showMaxMin(false)
    , legend = nv.models.legend()
    , dispatch = d3.dispatch('tooltipShow', 'tooltipHide')
  ;

  var showTooltip = function(e, offsetElement) {
    // New addition to calculate position if SVG is scaled with viewBox, may move TODO: consider implementing everywhere else
    if (offsetElement) {
      var svg = d3.select(offsetElement).select('svg');
      var viewBox = svg.attr('viewBox');
      if (viewBox) {
        viewBox = viewBox.split(' ');
        var ratio = parseInt(svg.style('width'),10) / viewBox[2];
        e.pos[0] = e.pos[0] * ratio;
        e.pos[1] = e.pos[1] * ratio;
      }
    }

    var left = e.pos[0] + ( $(offsetElement).offset().left || 0 ),
        top = e.pos[1] + ( $(offsetElement).offset().top || 0),
        x = e.point.x,
        y = e.point.y,
        content = tooltip(e.series.key, x, y, e, chart);

    nv.tooltip.show([left, top], content, null, 40, offsetElement);
  };

  var bubbleClick = function(e) {
    return;
  };

  var getAbsoluteXY = function (element) {
    var viewportElement = document.documentElement
      , box = element.getBoundingClientRect()
      , scrollLeft = viewportElement.scrollLeft + document.body.scrollLeft
      , scrollTop = viewportElement.scrollTop + document.body.scrollTop
      , x = box.left + scrollLeft
      , y = box.top + scrollTop;

    return {"x": x, "y": y};
  };

  //============================================================


  function chart(selection) {

    selection.each(function(chartData) {

      var properties = chartData.properties
        , data = chartData.data;

      var container = d3.select(this),
          that = this;

      var width = width  || parseInt(container.style('width'),10)
        , height = height || parseInt(container.style('height'),10);

      var availableWidth = (width || 960) - margin.left - margin.right,
          availableHeight = (height || 400) - margin.top - margin.bottom;

      function getTimeTicks(data) {
        function daysInMonth(date) {
          return 32 - new Date(date.getFullYear(), date.getMonth(), 32).getDate();
        }
        var timeExtent =
              d3.extent(d3.merge(
                  data.map(function(d) {
                    return d.values.map(function(d,i) {
                      return format.parse(getX(d));
                    });
                  })
                )
              );
        var timeRange =
              d3.time.month.range(
                d3.time.month.floor(timeExtent[0]),
                d3.time.month.ceil(timeExtent[1])
              );
        var timeTicks =
              timeRange.map(function(d) {
                return d3.time.day.offset( d3.time.month.floor(d), -1+daysInMonth(d)/2 );
              });
        return timeTicks;
      }

      function getGroupTicks(data) {
        var yValues = data.map(function(s){
            return d3.min( s.values.map(function(p){ return p.y; } ) );
          });
        return yValues;
      }

      var gHeight = availableHeight/data.length
        , gOffset = gHeight*0.25
        , gDomain = [0,1]
        , gRange = [0,1]
        , gScale = d3.scale.linear().domain(gDomain).range(gRange);

      var total = 0;

      //add series index to each data point for reference
      data = data.map(function(s, i) {
          s.total = 0;

          s.values = s.values.sort(function(a, b) {
              return b.y < a.y ? -1 : b.y > a.y ? 1 : 0;
            })
            .map(function(p) {
              s.total += p.y;
              return p;
            });

          s.group = i;
          return s;
        })
        .sort(function(a, b) {
          return a.total < b.total ? -1 : a.total > b.total ? 1 : 0;
        })
        .map(function(s, i) {
          total += s.total;

          gDomain = d3.extent( s.values.map(function(p){ return p.y; }) );
          gRange = [gHeight*i+gOffset, gHeight*(i+1)-gOffset];
          gScale.domain(gDomain).range(gRange);

          s.values = s.values
            .map(function(p) {
              //p.series = p.probability;
              p.group = s.group;
              p.opportunity = p.y;
              p.y = gScale(p.opportunity);
              return p;
            });

          return s;
        });

      properties.title = 'Total = $' + d3.format(',.02d')(total);

      //------------------------------------------------------------
      // Display noData message if there's nothing to show.

      if (!data || !data.length || !data.filter(function(d) { return d.values.length; }).length) {
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
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-bubbleChart').data([data]);
      var gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-bubbleChart').append('g');
      var g = wrap.select('g');

      gEnter.append('g').attr('class', 'nv-x nv-axis');
      gEnter.append('g').attr('class', 'nv-y nv-axis');
      gEnter.append('g').attr('class', 'nv-bubblesWrap');

      //------------------------------------------------------------
      // Title & Legend

      var titleHeight = 0
        , legendHeight = 0;

      if (showLegend)
      {
        gEnter.append('g').attr('class', 'nv-legendWrap');

        legend.width(width*(showTitle?0.7:1)-10);

        g.select('.nv-legendWrap')
          .datum(data)
          .call(legend);

        legendHeight = legend.height();

        if ( margin.top < Math.max(legendHeight, titleHeight) ) {
          margin.top = Math.max(legendHeight, titleHeight);
          availableHeight = (height || parseInt(container.style('height'),10) || 400)
                             - margin.top - margin.bottom;
        }

        g.select('.nv-legendWrap')
            .attr('transform', 'translate('+ ((width*(showTitle?0.3:0))-margin.left+10) +',' + (-margin.top) +')');
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

        titleHeight = parseInt( g.select('.nv-title').node().getBBox().height, 10 ) +
          parseInt( g.select('.nv-title').style('margin-top'), 10 ) +
          parseInt( g.select('.nv-title').style('margin-bottom'), 10 );

        if ( margin.top < Math.max(legendHeight, titleHeight) )
        {
          margin.top = Math.max(legendHeight, titleHeight);
          availableHeight = (height || parseInt(container.style('height'), 10) || 400)
                             - margin.top - margin.bottom;
        }

        g.select('.nv-titleWrap')
            .attr('transform', 'translate(0,' + (-margin.top+parseInt( g.select('.nv-title').node().getBBox().height, 10 ) ) +')');
      }

      //------------------------------------------------------------

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');


      //------------------------------------------------------------
      // Main Chart Component(s)

      bubbles
        .width(availableWidth)
        .height(availableHeight)
        //.margin(margin)
        .color(data.map(function(d,i) {
          return d.color || color(d, i);
        }).filter(function(d,i) { return !data[i].disabled; }));

      var bubblesWrap = g.select('.nv-bubblesWrap')
          .datum(data.filter(function(d) { return !d.disabled; }));

      d3.transition(bubblesWrap).call(bubbles);


      // x Axis

      xAxis
        .scale(x)
        .ticks( d3.time.months, 1 )
        .tickSize(0)
        .tickValues(getTimeTicks(data))
        .showMaxMin(false)
        .tickFormat(function(d) {
          return d3.time.format('%b')(new Date(d));
        });

      g.select('.nv-x.nv-axis')
          .attr('transform', 'translate(0,' + y.range()[0] + ')');

      d3.transition(g.select('.nv-x.nv-axis'))
          .call(xAxis);


      // y Axis

      yAxis
        .scale(y)
        .ticks( data.length )
        .tickValues( getGroupTicks(data) )
        .tickSize(-availableWidth, 0)
        .tickFormat(function(d,i){ return data[i].key; });

      d3.transition(g.select('.nv-y.nv-axis'))
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

/*
      legend.dispatch.on('legendMouseover', function(d, i) {
        d.hover = true;
        selection.transition().call(chart)
      });

      legend.dispatch.on('legendMouseout', function(d, i) {
        d.hover = false;
        selection.transition().call(chart)
      });
*/

      dispatch.on('tooltipShow', function(e) {
        if (tooltips) showTooltip(e, that.parentNode);
      });

      //============================================================


      chart.update = function() { chart(selection); };
      chart.container = this;

    });

    return chart;
  }


  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  bubbles.dispatch.on('elementMouseover.tooltip', function(e) {
    e.pos = [e.pos[0], e.pos[1]];
    dispatch.tooltipShow(e);
  });

  bubbles.dispatch.on('elementMouseout.tooltip', function(e) {
    dispatch.tooltipHide(e);
  });

  dispatch.on('tooltipHide', function() {
    if (tooltips) nv.tooltip.cleanup();
  });

  bubbles.dispatch.on('elementClick', function(e) {
    bubbleClick(e);
  });

  //============================================================


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.bubbles = bubbles;
  chart.legend = legend;
  chart.xAxis = xAxis;
  chart.yAxis = yAxis;

  d3.rebind(chart, bubbles, 'defined', 'isArea', 'x', 'y', 'size', 'xScale', 'yScale', 'xDomain', 'yDomain', 'forceX', 'forceY', 'interactive', 'clipEdge', 'clipVoronoi', 'id', 'interpolate', 'color', 'gradient', 'useClass');

  chart.colorData = function(_) {
    if (arguments[0] === 'graduated')
    {
      var c1 = arguments[1].c1
        , c2 = arguments[1].c2
        , l = arguments[1].l;
      var color = function (d,i) { return d3.interpolateHsl( d3.rgb(c1), d3.rgb(c2) )(i/l); };
    }
    else if (_ === 'class')
    {
      chart.useClass(true);
      legend.useClass(true);
      var color = function (d,i) { return 'inherit'; };
    }
    else
    {
      var color = nv.utils.defaultColor();
    }

    legend.color(color);
    bubbles.color(color);

    return chart;
  };

  chart.colorFill = function(_) {
    if (_ === 'gradient')
    {
      var fill = function (d,i) { return chart.gradient()(d,i); };
    }
    else
    {
      var fill = chart.color();
    }

    bubbles.fill(fill);

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

  chart.tooltips = function(_) {
    if (!arguments.length) return tooltips;
    tooltips = _;
    return chart;
  };

  chart.tooltip = function(_) {
    if (!arguments.length) return tooltip;
    tooltip = _;
    return chart;
  };

  chart.noData = function(_) {
    if (!arguments.length) return noData;
    noData = _;
    return chart;
  };

  chart.bubbleClick = function(_) {
    if (!arguments.length) return bubbleClick;
    bubbleClick = _;
    return chart;
  };
  //============================================================


  return chart;
};
