// Donut Chart
nv.addGraph(function() {
  var chart = nv.models.pieChart()
    .x(function(d) { return d.key })
    .y(function(d) { return d.value })
    .showLabels(true)
    .showTitle(false)
    .donut(true)
    .donutLabelsOutside(true)
    .colorData( 'graduated', {c1: '#C7E8F7', c2: '#023B68', l: pie_data.data.length} )
    .colorFill( 'default' )
    .tooltip( function(key, x, y, e, graph) {
      return '<p>Interaction: <b>' + key + '</b></p>' +
             '<p>Events: <b>' +  parseInt(y) + '</b></p>';
    });

  d3.select("#donut svg")
    .datum(pie_data)
    .transition().duration(1200)
    .call(chart);

  nv.utils.windowResize(function(){ chart.update(); });

  //attachToggleExpand(chart);

  return chart;
});
