nv.addGraph(function() {

  var zoomExtents = { 'min': 0.25, 'max': 2 };

  var chart = nv.models.tree()
        .zoomExtents(zoomExtents);

  d3.select("#org svg")
      .datum(tree_data)
    .transition().duration(700)
      .call(chart);

  nv.utils.windowResize(function(){ chart.resize(); });

  attachToggleExpand("#org svg", chart);

  $('.dashlet-group a[href="#show-all-nodes"]').click(function(){ chart.showall() });
  $('.dashlet-group a[href="#zoom-to-fit"]').click(function(){ chart.reset() });

  // minus
  $('.btn-slider a[href="#zoom-in"]').click(function(){ 
    var scale = chart.zoomIn(0.25)
      , limit = (scale === zoomExtents.max);
    $(this).toggleClass('disabled', limit);
    $('.btn-slider a[href="#zoom-out"]').toggleClass('disabled', false);
    $('#sliderChartZoom').noUiSlider('move', {to: scale*100});
  });
  //plus
  $('.btn-slider a[href="#zoom-out"]').click(function(){ 
    var scale = chart.zoomOut(0.25)
      , limit = (scale === zoomExtents.min);
    $(this).toggleClass('disabled', limit);
    $('.btn-slider a[href="#zoom-in"]').toggleClass('disabled', false);
    $('#sliderChartZoom').noUiSlider('move', {to: scale*100});
  });

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

  return chart;
});


