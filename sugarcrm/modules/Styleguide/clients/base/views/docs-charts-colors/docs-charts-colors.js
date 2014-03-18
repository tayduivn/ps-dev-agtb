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

  // charts colors
  _renderHtml: function () {
    this._super('_renderHtml');

    var gauge_data_1 = {
      'properties': {
        'title': 'Closed Won Opportunities Gauge',
        'value': 4
      },
      'data': [
        {
          key: "Range 1"
          , y: 3
        },
        {
          key: "Range 2"
          , y: 5
        },
        {
          key: "Range 3"
          , y: 7
        },
        {
          key: "Range 4"
          , y: 9
        }
      ]
    };

    var gauge_data_2 = {
      'properties': {
        'title': 'Closed Won Opportunities Gauge',
        'value': 4
      },
      'data': [
        {
          key: "Range 1"
          , y: 3
          , color: "#d62728"
        },
        {
          key: "Range 2"
          , y: 5
          , color: "#ff7f0e"
        },
        {
          key: "Range 3"
          , y: 7
          , color: "#bcbd22"
        },
        {
          key: "Range 4"
          , y: 9
          , color: "#2ca02c"
        }
      ]
    };

    var gauge_data_3 = {
      'properties': {
        'title': 'Closed Won Opportunities Gauge',
        'value': 4
      },
      'data': [
        {
          key: "Range 1"
          , y: 3
          , class: "nv-fill07"
        },
        {
          key: "Range 2"
          , y: 5
          , class: "nv-fill03"
        },
        {
          key: "Range 3"
          , y: 7
          , class: "nv-fill17"
        },
        {
          key: "Range 4"
          , y: 9
          , class: "nv-fill05"
        }
      ]
    };

    // Gauge Chart
    nv.addGraph(function() {
      var gauge = nv.models.gaugeChart()
          .x(function(d) { return d.key })
          .y(function(d) { return d.y })
          .showLabels(true)
          .showTitle(true)
          .colorData( 'default' )
          .ringWidth(50)
          .maxValue(9)
          .transitionMs(4000);

      d3.select("#gauge1 svg")
          .datum(gauge_data_1)
          .call(gauge);

      //nv.utils.windowResize(gauge.update);
      return gauge;
    });

    nv.addGraph(function() {
      var gauge = nv.models.gaugeChart()
          .x(function(d) { return d.key })
          .y(function(d) { return d.y })
          .showLabels(true)
          .showTitle(true)
          .colorData( 'default', {gradient:true} )
          .ringWidth(50)
          .maxValue(9)
          .transitionMs(4000);

      d3.select("#gauge2 svg")
          .datum(gauge_data_1)
        .transition().duration(500)
          .call(gauge);

      //nv.utils.windowResize(gauge.update);
      return gauge;
    });

    nv.addGraph(function() {
      var gauge = nv.models.gaugeChart()
          .x(function(d) { return d.key })
          .y(function(d) { return d.y })
          .showLabels(true)
          .showTitle(true)
          .colorData( 'default' )
          .ringWidth(50)
          .maxValue(9)
          .transitionMs(4000);

      d3.select("#gauge3 svg")
          .datum(gauge_data_2)
        .transition().duration(500)
          .call(gauge);

      return gauge;
    });

    nv.addGraph(function() {
      var gauge = nv.models.gaugeChart()
          .x(function(d) { return d.key })
          .y(function(d) { return d.y })
          .showLabels(true)
          .showTitle(true)
          .colorData( 'default', {gradient:true} )
          .ringWidth(50)
          .maxValue(9)
          .transitionMs(4000);

      d3.select("#gauge4 svg")
          .datum(gauge_data_2)
        .transition().duration(500)
          .call(gauge);

      return gauge;
    });

    nv.addGraph(function() {
      var gauge = nv.models.gaugeChart()
          .x(function(d) { return d.key })
          .y(function(d) { return d.y })
          .showLabels(true)
          .showTitle(true)
          .colorData( 'graduated', {c1: '#e8e2ca', c2: '#3e6c0a', l: gauge_data_1.data.length} )
          .ringWidth(50)
          .maxValue(9)
          .transitionMs(4000);

      d3.select("#gauge5 svg")
          .datum(gauge_data_1)
        .transition().duration(500)
          .call(gauge);

      return gauge;
    });

    nv.addGraph(function() {
      var gauge = nv.models.gaugeChart()
          .x(function(d) { return d.key })
          .y(function(d) { return d.y })
          .showLabels(true)
          .showTitle(true)
          .colorData( 'graduated', {c1: '#e8e2ca', c2: '#3e6c0a', l: gauge_data_1.data.length, gradient:true} )
          .ringWidth(50)
          .maxValue(9)
          .transitionMs(4000);

      d3.select("#gauge6 svg")
          .datum(gauge_data_1)
        .transition().duration(500)
          .call(gauge);

      //nv.utils.windowResize(gauge.update);
      return gauge;
    });


    nv.addGraph(function() {
      var gauge = nv.models.gaugeChart()
          .x(function(d) { return d.key })
          .y(function(d) { return d.y })
          .showLabels(true)
          .showTitle(true)
          .colorData( 'class' )
          .ringWidth(50)
          .maxValue(9)
          .transitionMs(4000);

      d3.select("#gauge7 svg")
          .datum(gauge_data_1)
        .transition().duration(500)
          .call(gauge);

      return gauge;
    });

    nv.addGraph(function() {
      var gauge = nv.models.gaugeChart()
          .x(function(d) { return d.key })
          .y(function(d) { return d.y })
          .showLabels(true)
          .showTitle(true)
          .colorData( 'class', {gradient:true} )
          .ringWidth(50)
          .maxValue(9)
          .transitionMs(4000);

      d3.select("#gauge8 svg")
          .datum(gauge_data_3)
        .transition().duration(500)
          .call(gauge);

      //nv.utils.windowResize(gauge.update);
      return gauge;
    });
  }
})
