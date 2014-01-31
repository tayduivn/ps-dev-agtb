// charts line
function _render_content(view, app) {
  // Line chart
  nv.addGraph(function() {
    var chart = nv.models.lineChart()
          .x(function(d) { return d[0] })
          .y(function(d) { return d[1] })
          .showTitle(false)
          .tooltips(true)
          .showControls(false)
          .tooltipContent( function(key, x, y, e, graph) {
              return '<p>Category: <b>' + key + '</b></p>' +
                     '<p>Amount: <b>$' +  parseInt(y) + 'M</b></p>' +
                     '<p>Date: <b>' +  x + '</b></p>'
            })
          //.forceY([0,400]).forceX([0,6]);
        ;

    chart.xAxis
        .tickFormat(function(d) { return d3.time.format('%x')(new Date(d)) });

    chart.yAxis
        .axisLabel('Voltage (v)')
        .tickFormat(d3.format(',.2f'));

    d3.select('#line1 svg')
        .datum(line_data_default)
      .transition().duration(500)
        .call(chart);

    return chart;
  });

  // Stacked area chart
  nv.addGraph(function() {

    var chart = nv.models.stackedAreaChart()
          .x(function(d) { return d[0] })
          .y(function(d) { return d[1] })
          .tooltipContent( function(key, x, y, e, graph) {
              return '<p>Category: <b>' + key + '</b></p>' +
                     '<p>Amount: <b>$' +  parseInt(y) + 'M</b></p>' +
                     '<p>Date: <b>' +  x + '</b></p>'
            })
          .showTitle(false)
          .tooltips(true)
          .showControls(false)
          .colorData( 'graduated', {c1: '#e8e2ca', c2: '#3e6c0a', l: line_data_default.data.length} )
          //.colorData( 'class' )
          //.colorData( 'default' )
          //.clipEdge(true)
        ;

    chart.xAxis
        .tickFormat(function(d) { return d3.time.format('%x')(new Date(d)) });

    chart.yAxis
        .axisLabel('Expenditures ($)')
        .tickFormat(d3.format(',.2f'));

    d3.select('#area svg')
        .datum(line_data_default)
      .transition().duration(500)
        .call(chart);

    return chart;
  });
}

function _dispose_content(view) {
}
