nv.addGraph(function() {

  var zoomExtents = { 'min': 0.25, 'max': 2 }
    , nodeRenderer = function(d) { // custom renderer for node
        return '<img src="../img/'+ d.image +'" class="rep-avatar" width="32" height="32"><div class="rep-name">'+ d.name +'</div><div class="rep-title">'+ d.title + '</div>';
      }
  ;

  var chart = nv.models.tree()
        .duration(300)
        .nodeSize({ 'width': 124, 'height': 56 })
        .nodeRenderer(nodeRenderer)
        .zoomExtents(zoomExtents)
        .horizontal(false);

  d3.json("../js/nvd3/data/tree_data.json", function(json) {
    var tree_data = json;

    tree_data.x0 = 0;
    tree_data.y0 = 0;

    d3.select("#org svg")
        .datum(tree_data)
      .transition().duration(700)
        .call(chart);
  });

  nv.utils.windowResize(function(){ chart.resize(); });

  //defined in /styleguide/sugar7/partial/widget/widget-control.html
  attachToggleExpand("#org svg", chart);

  // toggle buttons
  $('.dashlet-group a[href="#orientation"]').click(function(){
    chart.orientation();
    $(this).find('i').toggleClass('icon-arrow-right icon-arrow-down');
  });
  $('.dashlet-group a[href="#show-all-nodes"]').click(function(){
    chart.showall();
  });
  $('.dashlet-group a[href="#zoom-to-fit"]').click(function(){
    chart.reset();
    $('#sliderChartZoom').noUiSlider('move', {to: 100});
  });

  // zoom uiSlider control
  // minus
  $('.btn-slider a[href="#zoom-in"]').click(function(){
    var scale = chart.zoom(0.25)
      , limit = (scale === zoomExtents.max);
    $(this).toggleClass('disabled', limit);
    $('.btn-slider a[href="#zoom-out"]').toggleClass('disabled', false);
    $('#sliderChartZoom').noUiSlider('move', {to: scale*100});
  });
  //plus
  $('.btn-slider a[href="#zoom-out"]').click(function(){
    var scale = chart.zoom(-0.25)
      , limit = (scale === zoomExtents.min);
    $(this).toggleClass('disabled', limit);
    $('.btn-slider a[href="#zoom-in"]').toggleClass('disabled', false);
    $('#sliderChartZoom').noUiSlider('move', {to: scale*100});
  });
  //slider
  $('#sliderChartZoom').noUiSlider('init',
    {
      start: 100,
      knobs: 1,
      scale: [25,200],
      connect: false,
      step: 25,
      change: function(){
        var values = $(this).noUiSlider( 'value' )
          , scale = chart.zoomLevel(values[0]/100);
        $('.btn-slider a[href="#zoom-in"]').toggleClass('disabled', (scale === zoomExtents.max));
        $('.btn-slider a[href="#zoom-out"]').toggleClass('disabled', (scale === zoomExtents.min));
      }
    }
  );

  // jsTree control for selecting root node
  $("#organization").jstree({
    // generating tree from json data
    "json_data" : {
        "data" : [
            {
                "data" : "Jim Brennan",
                "state" : "open",
                "metadata" : { id : 1 },
                "children" : [
                    {
                        "data" : "Will Westin",
                        "metadata" : { id : 2 },
                        "children" : [
                            {"data" : "Chris Olliver","metadata" : {id : 3}}
                        ]
                    },
                    {
                        "data" : "Sarah Smith",
                        "metadata" : { id : 4 },
                        "children" : [
                            {"data" : "Sally Bronsen","metadata" : {id : 5}},
                            {"data" : "Max Jensen","metadata" : {id : 6}}
                        ]
                    }
                ]
            }
        ]
    },
    // plugins used for this tree
    "plugins" : [ "json_data", "ui", "types" ],
    "core" : {
      "animation" : 0
    }
  })
  .bind("loaded.jstree", function (e) {
      // do stuff when tree is loaded
      $("#organization").addClass("jstree-sugar");
      $("#organization > ul").addClass("list");
      $("#organization > ul > li > a").addClass("jstree-clicked");
  })
  .bind("select_node.jstree", function (e, data) {
      // do stuff when a node is selected
      // console.log("load data for id: " + jQuery.data(data.rslt.obj[0], "id"));
      chart.filter(jQuery.data(data.rslt.obj[0], "id"));
      $("#org-jstree").find('.dropdown-toggle span:first-child').text(data.inst.get_text());
      data.inst.toggle_node(data.rslt.obj);
  })
  .bind("click", function(e){
      e.stopPropagation();
      e.preventDefault();
  });

  return chart;
});
