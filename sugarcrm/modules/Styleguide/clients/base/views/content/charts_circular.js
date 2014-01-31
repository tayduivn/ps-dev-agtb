// charts circular
function _render_content(view, app) {
  // Pie Chart
  nv.addGraph(function() {
    var chart = nv.models.pieChart()
          .x(function(d) { return d.key })
          .y(function(d) { return d.value })
          .showLabels(true)
          .showTitle(false)
          //.color(d3.scale.category10().range())
          //.colorData( 'graduated', {c1: '#e8e2ca', c2: '#3e6c0a', l: pie_data.data.length} )
          //.colorData( 'class' )
          .colorData( 'default' )
          .tooltipContent( function(key, x, y, e, graph) {
            return '<p>Stage: <b>' + key + '</b></p>' +
                   '<p>Amount: <b>$' +  parseInt(y) + 'K</b></p>' +
                   '<p>Percent: <b>' +  x + '%</b></p>'
            })
        ;

      d3.select("#pie svg")
          .datum(pie_data_default)
        .transition().duration(500)
          .call(chart);

    return chart;
  });

  // Donut Chart
  nv.addGraph(function() {
    var chart = nv.models.pieChart()
          .x(function(d) { return d.key })
          .y(function(d) { return d.value })
          .showLabels(true)
          .showTitle(false)
          .donut(true)
          .donutRatio(0.4)
          .donutLabelsOutside(true)
          .hole(10)
          //.color(d3.scale.category10().range())
          //.colorData( 'graduated', {c1: '#e8e2ca', c2: '#3e6c0a', l: pie_data.data.length} )
          //.colorData( 'class' )
          .colorData( 'default' )
          .tooltipContent( function(key, x, y, e, graph) {
            return '<p>Stage: <b>' + key + '</b></p>' +
                   '<p>Amount: <b>$' +  parseInt(y) + 'K</b></p>' +
                   '<p>Percent: <b>' +  x + '%</b></p>'
            })
        ;

      d3.select("#donut svg")
          .datum(pie_data_default)
        .transition().duration(1200)
          .call(chart);

    return chart;
  });
}

function _dispose_content(view) {
}
