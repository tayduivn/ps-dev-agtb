
nv.models.bubble = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 0, right: 0, bottom: 0, left: 0}
    , scatter = nv.models.scatter()
    , width = 960
    , height = 500
    //, x = scatter.xScale()
    , x = d3.time.scale()
    , y = scatter.yScale()
    , id = Math.floor(Math.random() * 10000) //Create semi-unique ID incase user doesn't select one
    , getX = function(d) { return d.x; } // accessor to get the x value from a data point
    , getY = function(d) { return d.y; } // accessor to get the y value from a data point
    , forceY = [0] // 0 is forced by default.. this makes sense for the majority of bar graphs... user can always do chart.forceY([]) to remove
    , clipEdge = false // if true, masks lines within x and y scale
    , xDomain
    , yDomain
    , color = nv.utils.defaultColor() // a function that returns a color
    , fill = function (d,i) { return color(d,i); }
    , gradient = function (d,i) { return color(d,i); }
    , useClass = false
    , classStep = 1
    , dispatch = d3.dispatch('chartClick', 'elementClick', 'elementDblClick', 'elementMouseover', 'elementMouseout')
    , format = d3.time.format("%Y-%m-%d")
  ;


  //============================================================


  //============================================================
  // Private Variables
  //------------------------------------------------------------


  //============================================================

  function chart(selection) {
    selection.each(function(data) {
      var availableWidth = width - margin.left - margin.right,
          availableHeight = height - margin.top - margin.bottom,
          container = d3.select(this),
          fillGradient = function(d,i) {
            return nv.utils.colorLinearGradient( d, i, 'vertical', color(d,i), wrap.select('defs') );
          }
        ;

      chart.gradient( fillGradient );


      //------------------------------------------------------------
      // Setup Scales
      // remap and flatten the data for use in calculating the scales' domains

      var timeExtent =
            d3.extent(
              d3.merge(
                data.map(function(d) {
                  return d.values.map(function(d,i) {
                    return format.parse(d.x);
                  });
                })
              )
            );
      var xD = [
            d3.time.month.floor(timeExtent[0]),
            d3.time.day.offset(d3.time.month.ceil(timeExtent[1]),-1)
          ];

      var yD = d3.extent(
            d3.merge(
              data.map(function(d) {
                return d.values.map(function(d,i) {
                  return getY(d,i);
                });
              })
            ).concat(forceY)
          );

      scatter
        .id(id)
        .size(function(d){ return d.opportunity; }) // default size
        //.sizeDomain([16,256]) //set to speed up calculation, needs to be unset if there is a custom size accessor
        .sizeRange([256,2048])
        .singlePoint(true)
        .xScale(x)
        .xDomain(xD)
        .yScale(y)
        .yDomain(yD)
        .width(availableWidth)
        .height(availableHeight)
        //.margin(margin)
        .id(chart.id())
      ;

      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-bubble').data([data]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-bubble');
      var gEnter = wrapEnter.append('g');
      var g = wrap.select('g');

      gEnter.append('g').attr('class', 'nv-bubbleWrap');

      //------------------------------------------------------------

      var bubbleWrap = wrap.select('.nv-bubbleWrap');

      d3.transition(bubbleWrap).call(scatter);

    });

    return chart;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  chart.dispatch = scatter.dispatch;
  chart.scatter = scatter;

  d3.rebind(chart, scatter, 'interactive', 'size', 'xScale', 'yScale', 'zScale', 'xDomain', 'yDomain', 'sizeDomain', 'forceX', 'forceY', 'forceSize', 'clipVoronoi', 'clipRadius', 'color', 'gradient', 'useClass');

  chart.color = function(_) {
    if (!arguments.length) return color;
    color = _;
    scatter.color(color);
    return chart;
  };
  chart.fill = function(_) {
    if (!arguments.length) return fill;
    fill = _;
    scatter.fill(fill);
    return chart;
  };
  chart.gradient = function(_) {
    if (!arguments.length) return gradient;
    gradient = _;
    scatter.gradient(_);
    return chart;
  };
  chart.useClass = function(_) {
    if (!arguments.length) return useClass;
    useClass = _;
    scatter.useClass(_);
    return chart;
  };
  chart.classStep = function(_) {
    if (!arguments.length) return classStep;
    classStep = _;
    scatter.classStep(_);
    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) return getX;
    getX = _;
    scatter.x(_);
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) return getY;
    getY = _;
    scatter.y(_);
    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) return margin;
    margin.top    = typeof _.top    != 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  != 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom != 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   != 'undefined' ? _.left   : margin.left;
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

  chart.id = function(_) {
    if (!arguments.length) return id;
    id = _;
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
