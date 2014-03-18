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

  // charts line
  _renderHtml: function () {
    this._super('_renderHtml');

    // Line chart
    d3.json("styleguide/content/charts/data/line_data.json", function(data) {
      nv.addGraph(function() {
        var chart = nv.models.lineChart()
              .x(function(d) { return d[0]; })
              .y(function(d) { return d[1]; })
              .showTitle(false)
              .tooltips(true)
              .showControls(false)
              .tooltipContent( function(key, x, y, e, graph) {
                  return '<p>Category: <b>' + key + '</b></p>' +
                         '<p>Amount: <b>$' +  parseInt(y, 10) + 'M</b></p>' +
                         '<p>Date: <b>' +  x + '</b></p>';
                })
              //.forceY([0,400]).forceX([0,6]);
            ;

        chart.xAxis
            .tickFormat(function(d) { return d3.time.format('%x')(new Date(d)); });

        chart.yAxis
            .axisLabel('Voltage (v)')
            .tickFormat(d3.format(',.2f'));

        d3.select('#line1 svg')
            .datum(data)
          .transition().duration(500)
            .call(chart);

        return chart;
      });
    });

    // Stacked area chart
    d3.json("styleguide/content/charts/data/line_data.json", function(data) {
      nv.addGraph(function() {

        var chart = nv.models.stackedAreaChart()
              .x(function(d) { return d[0]; })
              .y(function(d) { return d[1]; })
              .tooltipContent( function(key, x, y, e, graph) {
                  return '<p>Category: <b>' + key + '</b></p>' +
                         '<p>Amount: <b>$' +  parseInt(y, 10) + 'M</b></p>' +
                         '<p>Date: <b>' +  x + '</b></p>';
                })
              .showTitle(false)
              .tooltips(true)
              .showControls(false)
              .colorData( 'graduated', {c1: '#e8e2ca', c2: '#3e6c0a', l: data.data.length} )
              //.colorData( 'class' )
              //.colorData( 'default' )
              //.clipEdge(true)
            ;

        chart.xAxis
            .tickFormat(function(d) { return d3.time.format('%x')(new Date(d)); });

        chart.yAxis
            .axisLabel('Expenditures ($)')
            .tickFormat(d3.format(',.2f'));

        d3.select('#area svg')
            .datum(data)
          .transition().duration(500)
            .call(chart);

        return chart;
      });
    });
  }
})
