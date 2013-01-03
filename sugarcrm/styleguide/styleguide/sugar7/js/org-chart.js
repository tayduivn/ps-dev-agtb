// all hail, stepheneb
// https://gist.github.com/1182434
SimpleGraph = function(elemid, options, json_path) {
  var self = this;

  this.container = d3.select(elemid).node();

  this.options = options || {};
  this.options.r = options.r || 5.5;
  this.options.duration = options.duration || 700;
  this.options.padding = options.padding || { top:0, right: 0, bottom:0, left: 0 };
  this.options.offset = options.offset || { top: 0, left: 0};
  this.options.nodesize = options.nodesize || { width: 115, height: 42};
  this.options.nodeimgpath = options.nodeimgpath || '../img/';

  this.size = {
    "width":  this.container.clientWidth - this.options.padding.left - this.options.padding.right,
    "height": this.container.clientHeight - this.options.padding.top  - this.options.padding.bottom
  };

  this.options.offset.top = this.options.offset.top+this.options.nodesize.height;
  this.zoom = 1;
  this.scale = 1;
  this.trans = [ 0, 0 ];
  this.shift = [ 0, 0 ];
  this.bbox = false;

  this.diagonal = d3.svg.diagonal();

  this.svg = d3.select(this.container).append("svg:svg")
    .append("g")
      .attr("transform", "translate("+ 0 +","+ 0 +")")
      .call(d3.behavior.zoom().scaleExtent([.25, 2]).on("zoom", function () {
        self.zoom = d3.event.scale;
        self.trans = d3.event.translate;
        self.chart.attr("transform", "translate("+ [
          (d3.event.translate[0]+self.options.offset.left+self.shift[0])*self.scale,
          (d3.event.translate[1]+self.options.offset.top+self.shift[1])*self.scale
        ] +")scale("+ self.scale*self.zoom +")");
      }));

  this.backg = this.svg.append('svg:rect')
    .attr("id","backg")
    .attr("width", this.size.width)
    .attr("height", this.size.height)
    .style("fill", "transparent");

  this.chart = this.svg.append('g')
    .attr("id","vis")
    .attr("transform", "translate("+ this.trans +")");

  this.defs = this.svg.append('defs');
  this.dropShadow = nv.utils.dropShadow( 'cardShadow', this.defs, {height:'120%', offset: 1.5, blur:1} );

  this.root = {};
  this.load_data(json_path);

  nv.utils.windowResize(winresize(this));

  function winresize(context) {
    var self = context;

    return function() {
      self.size = {
        "width":  self.container.clientWidth-self.options.padding.left-self.options.padding.right,
        "height": self.container.clientHeight-self.options.padding.top-self.options.padding.bottom
      };

      self.svg.select('#backg')
        .attr("width", self.size.width)
        .attr("height", self.size.height);

      self.update();
    }
  }

  return this;
};


SimpleGraph.prototype.resize = function() {
  var self = this;
  return function() {
    self.size = {
      "width":  self.container.clientWidth-self.options.padding.left-self.options.padding.right,
      "height": self.container.clientHeight-self.options.padding.top-self.options.padding.bottom
    };

    self.svg.select('#backg')
      .attr("width", self.size.width)
      .attr("height", self.size.height);

    self.update();
  }
};

SimpleGraph.prototype.update = function(source) {
  var self = this;

  // Compute the new tree layout.

  var tree = d3.layout.tree()
        .size(null)
        .elementsize([this.options.nodesize.width,1])
        .separation( function separation(a,b) {
          return a.parent == b.parent ? 1 : 1;
        });

  var nodes = tree.nodes(self.root);

  //nodes = nodes.sort(function(a, b) { return (a.x+((6-a.depth)*10000)) - (b.x+((6-b.depth)*10000)); });

  var chartWidth = d3.min(nodes,function(d){return d.x})+d3.max(nodes,function(d){return d.x});
  var chartHeight = ( d3.min(nodes,function(d){return d.y})+d3.max(nodes,function(d){return d.y}) )*100 + (self.options.nodesize.height);
  self.scale = d3.min([ self.size.width/chartWidth, self.size.height/chartHeight ]);

  if (self.size.width/chartWidth < self.size.height/chartHeight) {
    //width controls, set center height
    self.shift = [ 0, ((self.size.height/self.scale*self.zoom)-chartHeight)/2 ];
  } else {
    self.shift = [ ((self.size.width/self.scale*self.zoom)-chartWidth)/2, 0 ];
  }
  self.chart.attr("transform", "translate("+ [
    (self.trans[0]+self.options.offset.left+self.shift[0])*self.scale,
    (self.trans[1]+self.options.offset.top+self.shift[1])*self.scale
  ] +")scale("+ self.scale*self.zoom +")");

  nodes.forEach(function(d) {
    d.y = d.depth * 100;
  });

  // Update the nodes…
  var node = self.chart.selectAll("g.nv-card")
      .data(nodes, function (d) {
        return d.id;
      });

  // Enter any new nodes at the parent's previous position.
  var nodeEnter = node.enter().append("svg:g")
      .attr("class", "nv-card")
      .attr("id",function(d){return "nv-card-"+ d.id})
      .on("click", function(d) {
        toggle(d);
        self.update(d);
      });

  // node content
  nodeEnter
    .append("image")
      .attr('class', 'nv-cardAvatar')
      .attr("xlink:href", function(d){return self.options.nodeimgpath + d.image})
      .attr("x", -54)
      .attr("y", -36)
      .attr("width", 32)
      .attr("height", 32)
      .style('opacity', 1e-6);
  nodeEnter
    .append('svg:text')
      .attr('class', 'nv-cardName')
      .attr('x', -18)
      .attr('y', -24)
      .attr('text-anchor', 'start')
      .text(function(d){return d.name})
      .style('fill-opacity', 1e-6)
      .style('font-size', 0.7+'em');
  nodeEnter
    .append('svg:text')
      .attr('class', 'nv-cardTitle')
      .attr('x', -18)
      .attr('y', -10)
      .attr('text-anchor', 'start')
      .text(function(d){return d.title})
      .style('fill-opacity', 1e-6)
      .style('font-size', 0.5+'em');

  // background box
  nodeEnter
    .insert('svg:path','.nv-cardAvatar')
      .attr('class', 'nv-cardBox')
      .attr("d", function(d) {
        if (!self.bbox) {
          self.bbox = nodeEnter.node().getBBox();
        }
        //(x, y, width, height, radius)
        return nv.utils.roundedRectangle(
          -((self.bbox.width+16)/2), -(self.bbox.height+4), self.bbox.width+16, self.bbox.height+6, 3
        )
      })
      .style('stroke-opacity', 1e-6)
      .style('fill-opacity', 1e-6)
      .style('filter',self.dropShadow);

  // node control
  var xcCircle = nodeEnter
        .append('svg:g').attr('class','nv-expcoll');
      xcCircle
        .append("svg:circle").attr('class','nv-circ-back')
          .attr("r",1e-6);
      xcCircle
        .append("svg:line").attr('class','nv-line-vert')
          .attr('x1',0).attr('y1',-(self.options.r-.5)).attr('x2',0).attr('y2',(self.options.r-.5))
          .style('stroke-opacity', 1e-6);
      xcCircle
        .append("svg:line").attr('class','nv-line-hrzn')
          .attr('x1',-(self.options.r-.5)).attr('y1',0).attr('x2',(self.options.r-.5)).attr('y2',0)
          .style('stroke-opacity', 1e-6);

  //Transition nodes to their new position.
  var nodeUpdate = node.transition()
          .duration(self.options.duration)
          .attr("transform", function(d) { return "translate("+ d.x +","+ d.y +")"; });
      nodeUpdate.select(".nv-circ-back")
          .attr("r", self.options.r)
          .style("stroke-opacity", function(d) { return d.children || d._children ? 1 : 0; })
          .style("fill", function(d) { return d._children ? "#777" : (d.children?'#bbb':"none"); });
      nodeUpdate.select(".nv-circ-frnt")
          .attr("r", self.options.r)
          .style("stroke-opacity", function(d) { return d.children || d._children ? 1 : 0; });
      nodeUpdate.select(".nv-line-vert")
          .style("stroke", function(d) { return d._children ? "#fff" : "none"; })
          .style("stroke-opacity", function(d) { return d._children ? 1 : 0; });
      nodeUpdate.select(".nv-line-hrzn")
          .style("stroke", function(d) { return d.children || d._children ? "#fff" : "none"; })
          .style("stroke-opacity", function(d) { return d.children || d._children ? 1 : 0; });
      nodeUpdate.selectAll("text")
          .style("fill-opacity", 1);
      nodeUpdate.select(".nv-cardBox")
          .style("stroke-opacity", 1)
          .style("fill-opacity", 1);
      nodeUpdate.select(".nv-cardAvatar")
          .style("opacity", 1);


  // Transition exiting nodes to the parent's new position.
  var nodeExit = node.exit().transition()
          .duration(self.options.duration)
          .attr("transform", function(d) { return "translate("+ source.x +","+ source.y +")"; })
          .remove();
      nodeExit.selectAll("circle")
          .attr("r", 1e-6);
      nodeExit.select(".nv-line-vert")
          .style("stroke-opacity", 1e-6);
      nodeExit.select(".nv-line-hrzn")
          .style("stroke-opacity", 1e-6);
      nodeExit.selectAll("text")
          .style("fill-opacity", 1e-6);
      nodeExit.select(".nv-cardBox")
          .style("stroke-opacity", 1e-6)
          .style("fill-opacity", 1e-6);
      nodeExit.select(".nv-cardAvatar")
          .style("opacity", 1e-6);


  // Update the links
  var link = self.chart.selectAll("path.link")
      .data(tree.links(nodes), function (d) {
        return d.source.id + "-" + d.target.id;
      });

  // Enter any new links at the parent's previous position.
  link.enter().insert("svg:path", "g")
      .attr("class", "link")
      .attr("d", function(d) {
        var o = {x: source.x0, y: source.y0};
        return self.diagonal({source: o, target: o});
      });

  // Transition links to their new position.
  link.transition()
      .duration(self.options.duration)
      .attr("d", self.diagonal);

  // // Transition exiting nodes to the parent's new position.
  link.exit().transition()
      .duration(self.options.duration)
      .attr("d", function(d) {
        var o = {x: source.x, y: source.y};
        return self.diagonal({source: o, target: o});
      })
      .remove();

  // Stash the old positions for transition.
  nodes
    .forEach(function(d) {
      d.x0 = d.x;
      d.y0 = d.y;
    });

  // Toggle children.
  function toggle(d) {
    if (d.children) {
      d._children = d.children;
      d.children = null;
    } else {
      d.children = d._children;
      d._children = null;
    }
  }

// console.log(nodes);
};

SimpleGraph.prototype.load_data = function(json_path) {
  var self = this;

  d3.json(json_path, function(json) {
    self.root = json;
    self.root.x0 = 0;
    self.root.y0 = 0;

    function toggleAll(d) {
      if (d.children) {
        d.children.forEach(toggleAll);
        toggle(d);
      }
    }
    // Initialize the display to show a few nodes.
    //root.children.forEach(toggleAll);
    //toggle(root.children[1]);

    self.update(self.root);
  });
};
