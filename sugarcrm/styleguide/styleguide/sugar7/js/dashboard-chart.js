// Donut Chart
nv.addGraph(function() {
  var chart = nv.models.pieChart()
    .x(function(d) { return d.key })
    .y(function(d) { return d.value })
    .showLabels(true)
    .showTitle(false)
    .donut(true)
    .donutLabelsOutside(true)
    .colorData( 'default' )
    .colorFill( 'default' )
    .tooltip( function(key, x, y, e, graph) {
      return '<p>Event: <b>' + key + '</b></p>' +
             '<p>Amount: <b>' +  parseInt(y) + '</b></p>' +
             '<p>Percent: <b>' +  x + '%</b></p>'
    });

    d3.select("#donut svg")
      .datum(pie_data)
      .transition().duration(1200)
      .call(chart);

    nv.utils.windowResize(function(){chart.update();});

    return chart;
});
