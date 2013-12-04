// charts vertical
function _render_content(view, app) {
  // Vertical Bar Chart without Line
  nv.addGraph(function() {

    var chart = nv.models.multiBarChart()
          .showTitle(false)
          .tooltips(true)
          .showControls(false)
          .colorData( 'default' )
          .tooltipContent( function(key, x, y, e, graph) {
              return '<p>Stage: <b>' + key + '</b></p>' +
                     '<p>Amount: <b>$' +  parseInt(y, 10) + 'K</b></p>' +
                     '<p>Percent: <b>' +  x + '%</b></p>';
              })
          //.forceY([0,400]).forceX([0,6]);
        ;

    d3.select('#vert1 svg')
        .datum(multibar_data_default)
      .transition().duration(500)
        .call(chart);

    nv.utils.windowResize(chart.update);

    return chart;
  });

  //Vertical Bar Chart with Line
  nv.addGraph({
    generate: function() {
        var chart = nv.models.paretoChart()
            .showTitle(false)
            .showLegend(true)
            .tooltips(true)
            .showControls(false)
            .stacked(true)
            .clipEdge(false)
            .colorData( 'default' )
            .yAxisTickFormat(function(d){ return '$' + d3.format(',.2s')(d); })
            .quotaTickFormat(function(d){ return '$' + d3.format(',.3s')(d); });
            // override default barClick function
            // .barClick( function(data,e,selection) {
            //     //if only one bar series is disabled
            //     d3.select('#vert2 svg')
            //       .datum(forecast_data_Manager)
            //       .call(chart);
            //   })

        d3.select('#vert2 svg')
          .datum(forecast_data_Rep)
          .call(chart);

        nv.utils.windowResize(chart.update);

        return chart;
    },
    callback: function(graph) {
      $('#log').text('Chart is loaded');
    }
  });
}

function _dispose_content(view) {
}
