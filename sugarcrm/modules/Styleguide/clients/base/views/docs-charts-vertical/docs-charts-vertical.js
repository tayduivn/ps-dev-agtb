/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
({
  className: 'container-fluid',

  // charts vertical
  _renderHtml: function () {
    this._super('_renderHtml');

    // Vertical Bar Chart without Line

    d3.json("styleguide/content/charts/data/multibar_data.json", function(data) {

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
            .datum(data)
          .transition().duration(500)
            .call(chart);

        nv.utils.windowResize(chart.update);

        return chart;
      });
    });

    //Vertical Bar Chart with Line
    d3.json("styleguide/content/charts/data/pareto_data_salesrep.json", function(data) {
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
              .datum(data)
              .call(chart);

            nv.utils.windowResize(chart.update);

            return chart;
        },
        callback: function(graph) {
          $('#log').text('Chart is loaded');
        }
      });
    });
  }
})
