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

  // charts horizontal
  _renderHtml: function () {
    this._super('_renderHtml');

    // Multibar Horizontal Chart
    d3.json("styleguide/content/charts/data/opportunities_data.json", function(data) {
      nv.addGraph({
        generate: function() {
          nv.addGraph(function() {
            var chart = nv.models.multiBarHorizontalChart()
                  .x(function(d) { return d.label })
                  .y(function(d) { return d.value })
                  .margin({top: 10, right: 10, bottom: 20, left: 90})
                  .showValues(true)
                  .showTitle(false)
                  .tooltips(true)
                  .stacked(true)
                  .showControls(false)
                  .tooltipContent( function(key, x, y, e, graph) {
                    return '<p>Outcome: <b>' + key + '</b></p>' +
                           '<p>Lead Source: <b>' +  x + '</b></p>' +
                           '<p>Amount: <b>$' +  parseInt(y) + 'K</b></p>'
                    })
                ;

            chart.yAxis
                .tickFormat(d3.format(',.2f'));

            d3.select('#horiz1 svg')
                .datum(data)
              .transition().duration(500)
                .call(chart);

            return chart;
          });
        },
        callback: function(graph) {
          $('#log').text('Chart is loaded');
        }
      });
    });

    // Multibar Horizontal Chart with Baseline
    d3.json("styleguide/content/charts/data/horizbar_data.json", function(data) {
      nv.addGraph({
        generate: function() {
          nv.addGraph(function() {
            var chart = nv.models.multiBarHorizontalChart()
                  .x(function(d) { return d.label })
                  .y(function(d) { return d.value })
                  .margin({top: 10, right: 10, bottom: 20, left: 80})
                  .showValues(true)
                  .showTitle(false)
                  .tooltips(true)
                  .showControls(false)
                  .stacked(false)
                  .tooltipContent( function(key, x, y, e, graph) {
                    return '<p>Outcome: <b>' + key + '</b></p>' +
                           '<p>Lead Source: <b>' +  x + '</b></p>' +
                           '<p>Amount: <b>$' +  parseInt(y) + 'K</b></p>'
                  })
                ;

            chart.yAxis
                .tickFormat(d3.format(',.2f'));

            d3.select('#horiz2 svg')
                .datum(data)
              .transition().duration(500)
                .call(chart);

            return chart;
          });
        },
        callback: function(graph) {
          $('#log').text('Chart is loaded');
        }
      });
    });
  }
})
