
nv.models.bubble = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------
var format = d3.time.format("%Y-%m-%d");
  var margin = {top: 0, right: 0, bottom: 0, left: 0}
    , scatter = nv.models.scatter()
    , width = 960
    , height = 500
    //, x = scatter.xScale()
    , x = d3.time.scale()
    , y = scatter.yScale()
    , id = Math.floor(Math.random() * 10000) //Create semi-unique ID incase user doesn't select one
    , getX = function(d) { return d.x } // accessor to get the x value from a data point
    , getY = function(d) { return d.y } // accessor to get the y value from a data point
    , forceY = [0] // 0 is forced by default.. this makes sense for the majority of bar graphs... user can always do chart.forceY([]) to remove
    , clipEdge = false // if true, masks lines within x and y scale
    , xDomain
    , yDomain
    , color = nv.utils.defaultColor() // a function that returns a color
    , fill = function (d,i) { return color(d,i); }
    , gradient = function (d,i) { return color(d,i); }
    , useClass = false
    , dispatch = d3.dispatch('chartClick', 'elementClick', 'elementDblClick', 'elementMouseover', 'elementMouseout')
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

var groupHeight = availableHeight/data.length;
var groupDomain = [0,1];
var groupRange = [0,1];
var groupScale = d3.scale.linear().domain(groupDomain).range(groupRange);

      //add series index to each data point for reference
      data = data.map(function(s, i) {
          s.total = 0;

          groupDomain = d3.extent( s.values.map(function(p){return p.y}) );
          groupRange =

          s.values = s.values.map(function(p) {
            p.series = i;
            p.y0 = groupScale(p.y);
            s.total += p.y;
            return p;
          });
          return s;
        })
        .sort(function(a, b) {
          return a.total < b.total ? -1 : a.total > b.total ? 1 : 0;
        })
        .map(function(s, i) {
          s.y0 = s.total + (i!==0?data[i-1].total:0);
          return s;
        });

      console.log(data)

      //------------------------------------------------------------
      // Setup Scales
      // remap and flatten the data for use in calculating the scales' domains
      var seriesData = (xDomain && yDomain) ? [] : // if we know xDomain and yDomain, no need to calculate
            data.map(function(d) {
              return d.values.map(function(d,i) {
                return { x: getX(d,i), y: getY(d,i), y0: d.y0 }
              })
            });

      scatter
        .id(id)
        .size(function(d){ return d.y; }) // default size
        //.sizeDomain([16,256]) //set to speed up calculation, needs to be unset if there is a custom size accessor
        .sizeRange([256,2048])
        .singlePoint(true)
        .xScale(x)
        //.xDomain(d3.extent(d3.merge(seriesData).map(function(d) { return d.x })))
        .xDomain([format.parse('2013-01-01'),format.parse('2013-03-31')])
        .yScale(y)
        .yDomain(d3.extent(d3.merge(seriesData).map(function(d) { return d.y }).concat(forceY)))
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
      var g = wrap.select('g')

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

  d3.rebind(chart, scatter, 'interactive', 'size', 'xScale', 'yScale', 'zScale', 'xDomain', 'yDomain', 'sizeDomain', 'forceX', 'forceY', 'forceSize', 'clipVoronoi', 'clipRadius');

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
