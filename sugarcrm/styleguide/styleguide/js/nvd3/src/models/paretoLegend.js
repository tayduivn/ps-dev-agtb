
nv.models.paretoLegend = function () {
  //'use strict';

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 5, right: 0, bottom: 5, left: 0}
    , width = 400
    , height = 20
    , getKey = function (d) { return d.key; }
    , align = true
    , dispatch = d3.dispatch('legendClick', 'legendDblclick', 'legendMouseover', 'legendMouseout')
    , color = nv.utils.defaultColor()
    , classes = function (d,i) { return ''; }
    ;

  //============================================================


  function chart(selection) {
    selection.each(function (data) {
      var availableWidth = width - margin.left - margin.right,
          container = d3.select(this);

      if (!data || !data.length || !data.values || !data.values || !data.filter(function (d) { return d.values.length; }).length) {
        return chart;
      } else {
        container.selectAll('g.nv-legend').remove();
      }

      //------------------------------------------------------------
      // Setup containers and skeleton of chart
      var wrap = container.selectAll('g.nv-legend').data([data]);
      var gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-legend').append('g');
      var g = wrap.select('g');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------


      var series = g.selectAll('.nv-series')
          .data(function (d) { return d; });
      var seriesEnter = series.enter().append('g').attr('class', 'nv-series')
          .on('mouseover', function (d,i) {
            dispatch.legendMouseover(d,i);  //TODO: Make consistent with other event objects
          })
          .on('mouseout', function (d,i) {
            dispatch.legendMouseout(d,i);
          })
          .on('click', function (d,i) {
            dispatch.legendClick(d,i);
          })
          .on('dblclick', function (d,i) {
            dispatch.legendDblclick(d,i);
          });


      if (data[0].type === 'bar')
      {
        seriesEnter.append('rect')
            .attr('class', function (d,i) {
              return this.getAttribute('class') || (useClass ? (d.class || 'nv-fill' + (i % 20 > 9 ? '' : '0') + i % 20) : '');
            })
            .attr('fill', function (d,i) { return color(d,i); })
            .attr('stroke', function (d,i) { return color(d,i); })
            .attr('stroke-width', 0)
            .attr('width', 10)
            .attr('height', 10)
            .attr('transform', 'translate(-5,-5)');
        seriesEnter.append('text')
          .text(getKey)
          .attr('text-anchor', 'start')
          .attr('dy', '.36em')
          .attr('dx', '8');
      }
      else
      {
        seriesEnter.append('circle')
            .attr('class', function (d,i) {
              return this.getAttribute('class') || (useClass ? (d.class || 'nv-fill' + (i % 20 > 9 ? '' : '0') + i % 20) : '');
            })
            .attr('fill', function (d,i) { return color(d,i); })
            .attr('stroke', function (d,i) { return color(d,i); })
            .attr('stroke-width', 0)
            .attr('r', 4)
            .attr('transform', 'translate(8,0)');
        seriesEnter.append('line')
            .attr('class', function (d,i) {
              return this.getAttribute('class') || (useClass ? (d.class || 'nv-stroke' + (i % 10 > 9 ? '' : '0') + i % 10) : '');
            })
            .attr('stroke', function (d,i) { return color(d,i); })
            .attr('stroke-width', 2)
            .attr('x0',0)
            .attr('x1',16)
            .attr('y0',0)
            .attr('y1',0);
        seriesEnter.append('text')
          .text(getKey)
          .attr('text-anchor', 'start')
          .attr('dy', '.36em')
          .attr('dx', '20');
      }

      series.classed('disabled', function (d) { return d.disabled; });
      series.exit().remove();


      //TODO: implement fixed-width and max-width options (max-width is especially useful with the align option)

      // NEW ALIGNING CODE, TODO: clean up
      if (align) {
        var seriesWidths = [];
        series.each(function (d,i) {
          seriesWidths.push(d3.select(this).select('text').node().getComputedTextLength() + 28); // 28 is ~ the width of the circle plus some padding
        });

        //nv.log('Series Widths: ', JSON.stringify(seriesWidths));

        var seriesPerRow = 0;
        var legendWidth = 0;
        var columnWidths = [];

        while (legendWidth < availableWidth && seriesPerRow < seriesWidths.length) {
          columnWidths[seriesPerRow] = seriesWidths[seriesPerRow];
          legendWidth += seriesWidths[seriesPerRow += 1];
        }


        while (legendWidth > availableWidth && seriesPerRow > 1) {
          columnWidths = [];
          seriesPerRow -= 1;

          for (k = 0; k < seriesWidths.length; k += 1) {
            if (seriesWidths[k] > (columnWidths[k % seriesPerRow] || 0)) {
              columnWidths[k % seriesPerRow] = seriesWidths[k];
            }
          }

          legendWidth = columnWidths.reduce(function (prev, cur, index, array) { return prev + cur; });
        }
        //console.log(columnWidths, legendWidth, seriesPerRow);

        var xPositions = [];
        for (var i = 0, curX = 0; i < seriesPerRow; i += 1) {
          xPositions[i] = curX;
          curX += columnWidths[i];
        }

        series
            .attr('transform', function (d, i) {
              return 'translate(' + xPositions[i % seriesPerRow] + ',' + (5 + Math.floor(i / seriesPerRow) * 20) + ')';
            });

        //position legend as far right as possible within the total width
        g.attr('transform', 'translate(' + (width - margin.right - legendWidth) + ',' + margin.top + ')');

        height = margin.top + margin.bottom + (Math.ceil(seriesWidths.length / seriesPerRow) * 20);

      } else {

        var ypos = 5,
            newxpos = 5,
            maxwidth = 0,
            xpos;
        series
            .attr('transform', function (d,i) {
              var length = d3.select(this).select('text').node().getComputedTextLength() + 28;
              xpos = newxpos;

              if (width < margin.left + margin.right + xpos + length) {
                newxpos = xpos = 5;
                ypos += 20;
              }

              newxpos += length;
              if (newxpos > maxwidth) {
                maxwidth = newxpos;
              }

              return 'translate(' + xpos + ',' + ypos + ')';
            });

        //position legend as far right as possible within the total width
        g.attr('transform', 'translate(' + (width - margin.right - maxwidth) + ',' + margin.top + ')');

        height = margin.top + margin.bottom + ypos + 15;

      }

    });

    return chart;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  chart.dispatch = dispatch;

  chart.margin = function (_) {
    if (!arguments.length) { return margin; }
    margin = _;
    return chart;
  };

  chart.width = function (_) {
    if (!arguments.length) { return width; }
    width = _;
    return chart;
  };

  chart.height = function (_) {
    if (!arguments.length) { return height; }
    height = _;
    return chart;
  };

  chart.key = function (_) {
    if (!arguments.length) { return getKey; }
    getKey = _;
    return chart;
  };

  chart.color = function (_) {
    if (!arguments.length) { return color; }
    color = nv.utils.getColor(_);
    return chart;
  };

  chart.classes = function (_) {
    if (!arguments.length) { return classes; }
    classes = _;
    return chart;
  };

  chart.align = function (_) {
    if (!arguments.length) { return align; }
    align = _;
    return chart;
  };

  //============================================================


  return chart;
};
