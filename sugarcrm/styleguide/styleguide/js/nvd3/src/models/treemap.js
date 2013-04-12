
nv.models.treemap = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 20, right: 0, bottom: 0, left: 0}
    , width = 0
    , height = 0
    , color = nv.utils.defaultColor() // a function that returns a color
    , id = Math.floor(Math.random() * 10000) //Create semi-unique ID incase user doesn't select one
    , getSize = function(d) { return d.size; } // accessor to get the size value from a data point
    , getName = function(d) { return d.name; } // accessor to get the name value from a data point
    , clipEdge = true // if true, masks lines within x and y scale
    , x //can be accessed via chart.xScale()
    , y //can be accessed via chart.yScale()
    , color = nv.utils.defaultColor()
    , fill = function (d,i) { return color(d,i); }
    , gradient = function (d,i) { return color(d,i); }
    , className = function(d,i) { return 'nv-fill' + (i%20>9?'':'0') + i%20; }
    , groups = []
    , useClass = false
    , leafClick = function () { return false; }
    , dispatch = d3.dispatch('chartClick', 'elementClick', 'elementDblClick', 'elementMouseover', 'elementMouseout', 'elementMousemove')

  //============================================================


  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var x0, y0 //used to store previous scales
      ;

  //============================================================


  function chart(selection) {
    selection.each(function(chartData) {

      var data = chartData[0];

      function reduceGroups(d) {
        var i, l, g = groups.length;
        if ( d.children && getName(d,g) && groups.indexOf(getName(d,g))===-1 )
        {
          groups.push(getName(d,g));
          l = d.children.length;
          for (i=0;i<l;i++) {
            reduceGroups(d.children[i]);
          }
        }
      }
      reduceGroups(data);

      var availableWidth = width - margin.left - margin.right
        , availableHeight = height - margin.top - margin.bottom
        , container = d3.select(this)
        , transitioning
        , fillGradient = function(d,i) {
            return nv.utils.colorLinearGradient( d, i, 'vertical', 'base', color(d,i), wrap.select('defs') );
          }
        ;
      chart.gradient( fillGradient );

      data.dx = availableWidth;
      data.dy = availableHeight;

      x = d3.scale.linear()
            .domain([0, availableWidth])
            .range([0, availableWidth]);

      y = d3.scale.linear()
            .domain([0, availableHeight])
            .range([0, availableHeight]);

      x0 = x0 || x;
      y0 = y0 || y;

      //------------------------------------------------------------

      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-treemap').data([data]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-treemap');
      var defsEnter = wrapEnter.append('defs');
      var gEnter = wrapEnter.append('g');
      var g = wrap.select('g');

      //wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------
      // Clip Path

      // defsEnter.append('clipPath')
      //     .attr('id', 'nv-edge-clip-' + id)
      //   .append('rect');
      // wrap.select('#nv-edge-clip-' + id + ' rect')
      //     .attr('width', width)
      //     .attr('height', height);
      // g.attr('clip-path', clipEdge ? 'url(#nv-edge-clip-' + id + ')' : '');


      //------------------------------------------------------------
      // Main Chart

      var grandparent = gEnter.append("g").attr("class", "nv-grandparent");

      grandparent.append("rect")
          //.attr("y", -margin.top)
          .attr("width", width)
          .attr("height", margin.top);

      grandparent.append("text")
          .attr("x", 6)
          .attr("y", 6)
          .attr("dy", '.75em');

      display(data);

      function display(d) {

          var treemap = d3.layout.treemap()
              .value(getSize)
              .sort(function(a, b) { return getSize(a) - getSize(b); })
              .round(false)

          layout(d);

          grandparent.datum(d.parent).on("click", transition).select("text").text(name(d));

          var g1 = gEnter.insert("g", ".nv-grandparent")
            .attr("class", "nv-depth")
            .attr("height",availableHeight)
            .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

          var g = g1.selectAll("g").data(d.children).enter().append("g");

          // Transition for nodes with children.
          g.filter(function(d) {
                return d.children;
            })
            .classed("nv-children", true)
            .on('click', transition);

          // Navigate for nodes without children (leaves).
          g.filter(function(d) {
                return !(d.children);
            })
            .on("click", leafClick);

          g.on('mouseover', function(d,i){
              d3.select(this).classed('hover', true);
              dispatch.elementMouseover({
                  point: d,
                  pointIndex: i,
                  pos: [d3.event.pageX, d3.event.pageY],
                  id: id
              });
            })
            .on('mouseout', function(d,i){
              d3.select(this).classed('hover', false);
              dispatch.elementMouseout();
            })
            .on('mousemove', function(d,i){
              dispatch.elementMousemove({
                  point: d,
                  pointIndex: i,
                  pos: [d3.event.pageX, d3.event.pageY],
                  id: id
              });
            });


          var child_rects = g.selectAll(".nv-child").data(function(d) {
                return d.children || [d];
            }).enter().append("rect").attr("class", "nv-child").call(rect);

          child_rects
            .on('mouseover', function(d,i){
              d3.select(this).classed('hover', true);
              dispatch.elementMouseover({
                  label: getName(d),
                  value: getSize(d),
                  point: d,
                  pointIndex: i,
                  pos: [d3.event.pageX, d3.event.pageY],
                  id: id
              });
            })
            .on('mouseout', function(d,i){
              d3.select(this).classed('hover', false);
              dispatch.elementMouseout();
            });

          g.append("rect")
            .attr("class", "nv-parent")
            .call(rect);

          g.append("text")
            .attr("dy", ".75em")
            .text(function(d){
                  return getName(d);
            }).call(text);


          function transition(d) {
              dispatch.elementMouseout();
              if (transitioning || !d) return;
              transitioning = true;

              var g2 = display(d),
                t1 = g1.transition().duration(750),
                t2 = g2.transition().duration(750);

              // Update the domain only after entering new elements.
              x.domain([d.x, d.x + d.dx]);
              y.domain([d.y, d.y + d.dy]);

              // Enable anti-aliasing during the transition.
              container.style("shape-rendering", null);

              // Draw child nodes on top of parent nodes.
              container.selectAll(".nv-depth").sort(function(a, b) { return a.depth - b.depth; });

              // Fade-in entering text.
              g2.selectAll("text").style("fill-opacity", 0);

              // Transition to the new view.
              t1.selectAll("text").call(text).style("fill-opacity", 0);
              t2.selectAll("text").call(text).style("fill-opacity", 1);
              t1.selectAll("rect").call(rect);
              t2.selectAll("rect").call(rect);

              // Remove the old node when the transition is finished.
              t1.remove().each("end", function() {
                  container.style("shape-rendering", "crispEdges");
                  transitioning = false;
              });
          }

          function layout(d) {
              if(d.children) {
                  treemap.nodes({children: d.children});
                  d.children.forEach(function(c) {
                      c.x = d.x + c.x * d.dx;
                      c.y = d.y + c.y * d.dy;
                      c.dx *= d.dx;
                      c.dy *= d.dy;
                      c.parent = d;
                      layout(c);
                  });
              }
          }

          function text(t) {
              t.attr("x", function(d) { return x(d.x) + 6; })
                  .attr("y", function(d) { return y(d.y) + 6; });
          }

          function rect(r) {
              r.attr("x", function(d) { return x(d.x); })
                .attr("y", function(d) { return y(d.y); })
                .attr("width", function(d) { return x(d.x + d.dx) - x(d.x); })
                .attr("height", function(d) { return y(d.y + d.dy) - y(d.y); })
                .attr("class", function(d,i) {
                    // if (!el.classed('nv-parent'))
                    if ( useClass && className(d,i) )
                    {
                      d3.select(this).classed(className(d,i),true);
                    }
                    return this.getAttribute('class');
                })
                .attr('fill', function(d,i){
                  return (!d3.select(this).classed('nv-parent')) ? this.getAttribute('fill') || fill(d,groups.indexOf(className(d,i))) : this.getAttribute('fill');
                })
          }

          function name(d) {
              if(d.parent) {
                  return name(d.parent) + " / " + getName(d);
              }
              return getName(d);
          }

          return g;
      }

    });

    return chart;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  chart.dispatch = dispatch;


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
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) return getY;
    getY = _;
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

  chart.id = function(_) {
    if (!arguments.length) return id;
    id = _;
    return chart;
  };

  chart.leafClick = function(_) {
    if (!arguments.length) return leafClick;
    leafClick = _;
    return chart;
  };

  chart.getSize = function(_) {
    if (!arguments.length) return getSize;
    getSize = _;
    return chart;
  };

  chart.getName = function(_) {
    if (!arguments.length) return getName;
    getName = _;
    return chart;
  };

  chart.className = function(_) {
    if (!arguments.length) return className;
    className = _;
    return chart;
  };

  chart.groups = function(_) {
    if (!arguments.length) return groups;
    groups = _;
    return chart;
  };

  //============================================================


  return chart;
}
