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

  // charts circular
  _renderHtml: function () {
    this._super('_renderHtml');

    // Pie Chart
    d3.json("styleguide/content/charts/data/pie_data.json", function(data) {
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
              .datum(data)
            .transition().duration(500)
              .call(chart);

        return chart;
      });
    });

    // Donut Chart
    d3.json("styleguide/content/charts/data/pie_data.json", function(data) {
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
              .datum(data)
            .transition().duration(1200)
              .call(chart);

        return chart;
      });
    });
  }
})
