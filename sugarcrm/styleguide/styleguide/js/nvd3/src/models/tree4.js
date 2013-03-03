
nv.models.tree = function() {

  // ISSUES
  /*
  1. initial position of node 1 is not 848,0
  3. hover on circle only works on background
  */

  // all hail, stepheneb
  // https://gist.github.com/1182434
  // http://mbostock.github.com/d3/talk/20111018/tree.html
  // Compute the new tree layout.
  // https://groups.google.com/forum/#!topic/d3-js/-qUd_jcyGTw/discussion

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var id = Math.floor( Math.random() * 10000 ) //Create semi-unique ID in case user doesn't select one
    , color = nv.utils.defaultColor()
    , fill = function (d,i) { return color(d,i); }
    , gradient = function (d,i) { return color(d,i); }
    , fillGradient = function(d,i) {
        return nv.utils.colorRadialGradient( d, i, 0, 0, '35%', '35%', color(d,i), wrap.select('defs') );
      }
    , useClass = false
    , valueFormat = d3.format(',.2f')
    , showLabels = true
    , dispatch = d3.dispatch( 'chartClick', 'elementClick', 'elementDblClick', 'elementMouseover', 'elementMouseout' )
  ;

  // specific to org chart
  var r = 5.5
    , duration = 700
    , padding = { 'top': 10, 'right': 10, 'bottom': 10, 'left': 10 } // this is the distance from the edges of the svg to the chart
    , nodeSize = { 'width': 115, 'height': 42 }
    , nodeImgPath = '../img/'
  ;

  //============================================================

  function chart(selection)
  {
    selection.each(

    function(data) {

      var bbox = false;      // the actual size of a node

      var diagonal = d3.svg.diagonal();
      var zoom = d3.behavior.zoom().scaleExtent([.25, 2])
            .on('zoom', function() {
              gEnter.attr('transform',
                'translate('+ d3.event.translate +')scale('+ d3.event.scale +')'
              );
            });
      //------------------------------------------------------------
      // Setup svgs and skeleton of chart

      var svg = d3.select(this);
      var wrap = svg.selectAll('.nv-wrap').data([data]);
      var wrapEnter = wrap.enter().append('g')
            .attr('class', 'nvd3 nv-wrap nv-chart')
            .attr('id', 'nv-chart-' + id)
            .call( zoom );

      var defsEnter = wrapEnter.append('defs');
      var dropShadow = nv.utils.dropShadow('cardShadow', defsEnter, { 'height': '120%', 'offset': 1.5, 'blur': 1 });

      var backg = wrapEnter.append('svg:rect')
            .attr('id', 'backg')
            .attr('transform', 'translate('+ padding.left +','+ padding.top +')')
            .style('fill', 'transparent');

      var gEnter = wrapEnter.append('g');

      var treeChart = gEnter.append('g')
            .attr('class', 'nv-tree')
            .attr('id', 'vis');

      var tree = d3.layout.tree()
            .size(null)
            .elementsize([nodeSize.width,1])
            .separation( function separation(a,b) { return a.parent == b.parent ? 1 : 1; });

      var svgSize = { // the size of the svg container
          'width': parseInt( svg.style('width') )
        , 'height': parseInt( svg.style('height') )
      };

      //------------------------------------------------------------
      // svg
      //     .on('click', function(d,i) {
      //         dispatch.chartClick({
      //             data: d,
      //             index: i,
      //             pos: d3.event,
      //             id: id
      //         });
      //     });
      //------------------------------------------------------------

      chart.update = function(source) {

        var self = this;

        var nodes = tree.nodes(data);
             //.sort(function(a, b) { return (a.x+((6-a.depth)*10000)) - (b.x+((6-b.depth)*10000)); });

        var availableSize = { // the size of the svg container minus padding
            'width': svgSize.width - padding.left - padding.right
          , 'height': svgSize.height - padding.top  - padding.bottom
        };

        var chartSize = { // the size of the chart itself
            'width': d3.min(nodes, function(d){ return d.x; }) + d3.max(nodes, function(d){ return d.x; })
          , 'height': ( d3.min(nodes, function(d){ return d.y; }) + d3.max(nodes, function(d){ return d.y; }) ) * 100 + nodeSize.height
        };

        // initial chart scale to fit chart in container; set by chart.update
        var scale = d3.min([ availableSize.width/chartSize.width, availableSize.height/chartSize.height ])

        // initial chart translation to position chart in the center of container; set by chart.update
        if (availableSize.width/chartSize.width < availableSize.height/chartSize.height) {
          var shift = [ 0, ((availableSize.height/scale)-chartSize.height)/2 ];
        } else {
          var shift = [ ((availableSize.width/scale)-chartSize.width)/2, 0 ];
        }

        var offset = { 'top': padding.top + nodeSize.height, 'left': padding.left }  // this is needed because the origin of a node is at the bottom

        backg
          .attr('width', availableSize.width)
          .attr('height', availableSize.height);

        treeChart.attr('transform', 'translate('+ [
            (offset.left + shift[0]) * scale,
            (offset.top + shift[1]) * scale
          ] +')scale('+ scale +')');

        nodes.forEach(function(d) { d.y = d.depth * 100; });

        // Update the nodes…
        var node = treeChart.selectAll('g.nv-card').data(nodes, function(d){ return d.id; });

        // Enter any new nodes at the parent's previous position.
        var nodeEnter = node.enter().append('svg:g')
              .attr('class', 'nv-card')
              .attr('id', function(d){ return 'nv-card-'+ d.id; })
              .attr("transform", function(d) {
                if (d.parent) {
                  return "translate(" + d.parent.x0 + "," + d.parent.y0 + ")";
                } else {
                  //console.log(d)
                  return "translate(" + d.x0 + "," + d.y0 + ")";
                }
              })
              // 1. start transition at source position
              // .attr('x', function(d){ if (d.parent) { var p = d.parent; if (p.x) console.log(p.x); } return 220; })
              // .attr('y', function(d){ if (d.parent) { var p = d.parent; if (p.y) console.log(p.y); } return 220; })
              .on('click', function(d){ leafClick(d); });

        // node content
        nodeEnter
          .append('image')
            .attr('class', 'nv-cardAvatar')
            .attr('xlink:href', function(d) { return nodeImgPath + d.image; })
            .attr('x', -54)
            .attr('y', -36)
            .attr('width', 32)
            .attr('height', 32)
            .style('opacity', 1e-6);
        nodeEnter
          .append('svg:text')
            .attr('class', 'nv-cardName')
            .attr('x', -18)
            .attr('y', -24)
            .attr('text-anchor', 'start')
            .text(function(d) { return d.name; })
            .style('fill-opacity', 1e-6)
            .style('font-size', 0.7+'em');
        nodeEnter
          .append('svg:text')
            .attr('class', 'nv-cardTitle')
            .attr('x', -18)
            .attr('y', -10)
            .attr('text-anchor', 'start')
            .text(function(d) { return d.title; })
            .style('fill-opacity', 1e-6)
            .style('font-size', 0.5+'em');

        // node box
        nodeEnter
          .insert('svg:path', '.nv-cardAvatar')
            .attr('class', 'nv-cardBox')
            .attr('d', function(d) {
              if ( !self.bbox ) {
                self.bbox = nodeEnter.node().getBBox();
              }
              //(x, y, width, height, radius)
              return nv.utils.roundedRectangle(
                -((self.bbox.width+16)/2), -(self.bbox.height+4), self.bbox.width+16, self.bbox.height+6, 3
              );
            })
            .style('stroke-opacity', 1e-6)
            .style('fill-opacity', 1e-6)
            .style('filter', dropShadow);

        // node circle
        var xcCircle = nodeEnter.append('svg:g').attr('class', 'nv-expcoll');
            xcCircle.append('svg:circle').attr('class', 'nv-circ-back')
              .attr('r', 1e-6);
            xcCircle.append('svg:line').attr('class', 'nv-line-vert')
              .attr('x1', 0).attr('y1', .5-r).attr('x2', 0).attr('y2', r-.5)
              .style('stroke-opacity', 1e-6);
            xcCircle.append('svg:line').attr('class', 'nv-line-hrzn')
              .attr('x1', .5-r).attr('y1', 0).attr('x2', r-.5).attr('y2', 0)
              .style('stroke-opacity', 1e-6);
            xcCircle.append('svg:circle').attr('class', 'nv-circ-front')
              .attr('r', 1e-6);

        //Transition nodes to their new position.
        var nodeUpdate = node.transition()
              .duration(duration)
              .attr('transform', function(d) { return 'translate('+ d.x +','+ d.y +')'; });
            nodeUpdate.select('.nv-circ-back')
              .attr('r', r)
              .style('stroke-opacity', function(d) { return d.children || d._children ? 1 : 0; })
              .style('fill', function(d) { return d._children ? '#777' : (d.children?'#bbb':'none'); });
            nodeUpdate.select('.nv-circ-frnt')
              .attr('r', r)
              .style('stroke-opacity', function(d) { return d.children || d._children ? 1 : 0; });
            nodeUpdate.select('.nv-line-vert')
              .style('stroke', function(d) { return d._children ? '#fff' : 'none'; })
              .style('stroke-opacity', function(d) { return d._children ? 1 : 0; });
            nodeUpdate.select('.nv-line-hrzn')
              .style('stroke', function(d) { return d.children || d._children ? '#fff' : 'none'; })
              .style('stroke-opacity', function(d) { return d.children || d._children ? 1 : 0; });
            nodeUpdate.selectAll('text')
              .style('fill-opacity', 1);
            nodeUpdate.select('.nv-cardBox')
              .style('stroke-opacity', 1)
              .style('fill-opacity', 1);
            nodeUpdate.select('.nv-cardAvatar')
              .style('opacity', 1);

        // Transition exiting nodes to the parent's new position.
        var nodeExit = node.exit().transition()
              .duration(duration)
              .attr('transform', function(d) { return 'translate('+ source.x +','+ source.y +')'; })
              .remove();
            nodeExit.selectAll('circle')
              .attr('r', 1e-6);
            nodeExit.select('.nv-line-vert')
              .style('stroke-opacity', 1e-6);
            nodeExit.select('.nv-line-hrzn')
              .style('stroke-opacity', 1e-6);
            nodeExit.selectAll('text')
              .style('fill-opacity', 1e-6);
            nodeExit.select('.nv-cardBox')
              .style('stroke-opacity', 1e-6)
              .style('fill-opacity', 1e-6);
            nodeExit.select('.nv-cardAvatar')
              .style('opacity', 1e-6);

        // Update the links
        var link = treeChart.selectAll('path.link')
              .data(tree.links(nodes), function(d) {
                return d.source.id + '-' + d.target.id;
              });

        // Enter any new links at the parent's previous position.
        link.enter().insert('svg:path', 'g')
          .attr('class', 'link')
          .attr('d', function(d) {
            var o = { x: source.x0, y: source.y0 };
            return diagonal({ source: o, target: o });
          });

        // Transition links to their new position.
        link.transition()
          .duration(duration)
          .attr('d', diagonal);

        // Transition exiting nodes to the parent's new position.
        link.exit().transition()
          .duration(duration)
          .attr('d', function(d) {
            var o = { x: source.x, y: source.y };
            return diagonal({ source: o, target: o });
          })
          .remove();

        // Stash the old positions for transition.
        nodes
          .forEach(function(d) {
            d.x0 = d.x;
            d.y0 = d.y;
          });

        // Click tree node.
        function leafClick(d) {
          if (d.children) {
            d._children = d.children;
            d.children = null;
          } else {
            d.children = d._children;
            d._children = null;
          }

          chart.update(d);
        }

      };

      chart.resize = function() {
        svgSize = {
            'width': parseInt( svg.style('width') )
          , 'height': parseInt( svg.style('height') )
        };

        chart.update();
      }

      chart.gradient( fillGradient );
      chart.update(data);

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

  chart.values = function(_) {
    if (!arguments.length) return getValues;
    getValues = _;
    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) return getX;
    getX = _;
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) return getY;
    getY = d3.functor(_);
    return chart;
  };

  chart.showLabels = function(_) {
    if (!arguments.length) return showLabels;
    showLabels = _;
    return chart;
  };

  chart.id = function(_) {
    if (!arguments.length) return id;
    id = _;
    return chart;
  };

  chart.valueFormat = function(_) {
    if (!arguments.length) return valueFormat;
    valueFormat = _;
    return chart;
  };

  chart.labelThreshold = function(_) {
    if (!arguments.length) return labelThreshold;
    labelThreshold = _;
    return chart;
  };

  // ORG

  chart.radius = function(_) {
    if (!arguments.length) return r;
    r = _;
    return chart;
  };

  chart.duration = function(_) {
    if (!arguments.length) return duration;
    duration = _;
    return chart;
  };

  chart.padding = function(_) {
    if (!arguments.length) return padding;
    padding = _;
    return chart;
  };

  chart.nodeSize = function(_) {
    if (!arguments.length) return nodeSize;
    nodeSize = _;
    return chart;
  };

  chart.nodeImgPath = function(_) {
    if (!arguments.length) return nodeImgPath;
    nodeImgPath = _;
    return chart;
  };

  //============================================================

  return chart;
}
