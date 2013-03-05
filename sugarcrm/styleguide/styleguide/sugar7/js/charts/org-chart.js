nv.addGraph(function() {

  var chart = nv.models.tree();

  d3.select("#org svg")
      .datum(tree_data)
    .transition().duration(700)
      .call(chart);

  nv.utils.windowResize(function(){ chart.resize(); });

  attachToggleExpand("#org svg", chart);

  $('a[href="#show-all-nodes"]').click(function(){ chart.showall() });
  $('a[href="#zoom-to-fit"]').click(function(){ chart.reset() });

  return chart;
});

$('#sliderChartZoom').noUiSlider('init',
  {
    start: 1,
    knobs: 1,
    scale: [.25,2],
    connect: false,
    change: function(){
      var values = $(this).noUiSlider( 'value' );
      $(this).find('.noUi-lowerHandle .infoBox .tooltip-inner').text(values[0]+"%");
      $(this).find('.noUi-upperHandle .infoBox .tooltip-inner').text(values[1]+"%");
    }
  }
);
