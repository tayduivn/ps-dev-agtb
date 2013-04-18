      //instantiate D3 chart using NVD3
      var d3Chart = null
        , d3ChartContainer = d3.select('#db620e51-8350-c596-06d1-4f866bfcfd5b svg');

      nv.addGraph({
        generate: function() {
            var chart = nv.models.paretoChart()
                  //.margin({top: 0, right: 0, bottom: 40, left: 40})
                  .showControls(false)
                  .showTitle(false)
                  .stacked(false)
                  .colorData( 'default' );

            d3ChartContainer
              .datum(this.translateDataToD3(forecast_data_Q1))
              .transition().duration(500).call(chart);

            nv.utils.windowResize(chart.update);

            return chart;
        },
        translateDataToD3 : function( json, params )
        {
            return {
                'properties':{
                    'title': json.properties[0].title
                  , 'quota': parseInt(json.values[0].goalmarkervalue[0],10)
                  // bar group data (x-axis)
                  , 'groupData': json.values.map( function(d,i){
                        return {
                          'group': i
                        , 'l': json.values[i].label
                        , 't': json.values[i].values.reduce( function(p, c, i, a){
                            return parseInt(p,10) + parseInt(c,10);
                          })
                        }
                    })
                }
                // series data
              , 'data': json.label.map( function(d,i){
                    return {
                        'key': d
                      , 'type': 'bar'
                      , 'series': i
                      , 'values': json.values.map( function(e,j){
                            return { 'series': i, 'x': j+1, 'y': parseInt(e.values[i],10),  y0: 0 };
                        })
                      , 'valuesOrig': json.values.map( function(e,j){
                            return { 'series': i, 'x': j+1, 'y': parseInt(e.values[i],10),  y0: 0 };
                        })
                    }
                }).concat(
                    json.properties[0].goal_marker_label.filter( function(d,i){
                        return d !== 'Quota';
                      }).map( function(d,i){
                        return {
                            'key': d
                          , 'type': 'line'
                          , 'series': i
                          , 'values': json.values.map( function(e,j){
                              return { 'series': i, 'x': j+1, 'y': parseInt(e.goalmarkervalue[i+1],10) };
                            })
                          , 'valuesOrig': json.values.map( function(e,j){
                              return { 'series': i, 'x': j+1, 'y': parseInt(e.goalmarkervalue[i+1],10) };
                            })
                        }
                    })
                )
            };
        },
        callback: function(graph) {
          var self = this;
          d3Chart = graph;
          d3Chart.updateData = function (json,stacked){
            d3.select('#db620e51-8350-c596-06d1-4f866bfcfd5b svg')
              .datum(self.translateDataToD3(json))
              .transition().duration(500).call(d3Chart.stacked(stacked));
        }
      }
      });

      $('.thumbnail.viz .btn-expand-full').on('click', toggleExpand);

      function toggleExpand()
      {
          if ( $('.thumbnail.viz').hasClass('expanded') )
          {
              $('.thumbnail.viz').removeClass('expanded');
              $('.thumbnail.viz .nv-chart').css({'height':'300px'});
              $('.thumbnail.viz .btn-expand-full').addClass('btn-invisible');
              $('.thumbnail.viz .btn-expand-full span').removeClass('icon-resize-small');
          }
          else
          {
              $('.thumbnail.viz').addClass('expanded');
              $('.thumbnail.viz .nv-chart').css({'height':$(window).height()-168});
              $('.thumbnail.viz .btn-expand-full').removeClass('btn-invisible');
              $('.thumbnail.viz .btn-expand-full span').addClass('icon-resize-small');
          }
          d3Chart.update();
      }
