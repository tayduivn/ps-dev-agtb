nv.models.legend = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 10, right: 10, bottom: 10, left: 10}
    , width = 400
    , height = 20
    , radius = 5
    , gutter = 10
    , lineHeight = 20
    , align = 'right'
    , equalColumns = true
    , strings = {close: 'close', type: 'legend'}
    , id = Math.floor(Math.random() * 10000) //Create semi-unique ID in case user doesn't select one
    , getKey = function(d) { return d.key.length > 0 ? d.key : 'undefined'; }
    , color = nv.utils.defaultColor()
    , classes = function (d,i) { return ''; }
    , dispatch = d3.dispatch('legendClick', 'legendMouseover', 'legendMouseout', 'linkClick')
    ;

  // Private Variables
  //------------------------------------------------------------

  var legendOpen = 0;

  //============================================================

  function legend(selection) {
    selection.each(function(data) {
      var availableWidth = width - margin.left - margin.right
        , availableHeight = height - margin.top - margin.bottom
        , container = d3.select(this)
        , legendWidth = 0
        , legendHeight = 0;

      //------------------------------------------------------------
      // Setup containers and skeleton of legend

      var wrap = container.selectAll('g.nv-chart-legend').data([data]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'nv-chart-legend');

      var defs = wrapEnter.append('defs');
      defs
        .append('clipPath').attr('id', 'nv-edge-clip-' + id)
        .append('rect');
      var clip = wrap.select('#nv-edge-clip-' + id + ' rect');

      wrapEnter
        .append('rect').attr('class', 'nv-legend-background');
      var back = container.select('.nv-legend-background');

      wrapEnter
        .append('text').attr('class', 'nv-legend-link');
      var link = container.select('.nv-legend-link');

      wrapEnter
        .append('g').attr('class', 'nv-legend-mask')
        .append('g').attr('class', 'nv-legend');
      var mask = container.select('.nv-legend-mask');
      var g = container.select('g.nv-legend');

      var series = g.selectAll('.nv-series').data(function(d) { return d; });
      var seriesEnter = series.enter().append('g').attr('class', 'nv-series');

      var zoom = d3.behavior.zoom();

      function zoomLegend(d) {
        var trans = d3.transform(g.attr("transform")).translate
          , transX = trans[0]
          , transY = trans[1] + d3.event.sourceEvent.wheelDelta / 4
          , upMax = Math.max(transY, back.attr('height') - legendHeight); //should not go beyond diff
        if (upMax) {
          g .attr('transform', 'translate(' + transX + ',' + Math.min(upMax, 0) + ')');
        }
      }

      clip
        .attr('x', 0)
        .attr('y', 0)
        .attr('width', 0)
        .attr('height', 0);

      back
        .attr('x', 0)
        .attr('y', 0.5)
        .attr('rx', 2)
        .attr('ry', 2)
        .attr('width', 0)
        .attr('height', 0)
        .attr('filter', nv.utils.dropShadow('legend_back_' + id, defs, {blur: 2} ))
        .style('opacity', 0)
        .style('pointer-events', 'all');

      link
        .text(legendOpen === 1 ? legend.strings().close : legend.strings().type)
        .attr('text-anchor', align === 'right' ? 'end' : 'start')
        .attr('dy', '.32em')
        .attr('dx', 0)
        .attr('transform', 'translate(' + (align === 'right' ? width : 0) + ',' + (margin.top + radius) + ')')
        .style('opacity', 0)
        .on('click', function(d,i) {
          dispatch.linkClick(d,i);
        });

      mask
        .attr('clip-path', 'url(#nv-edge-clip-' + id + ')');

      seriesEnter
        .on('mouseover', function(d,i) {
          dispatch.legendMouseover(d,i);  //TODO: Make consistent with other event objects
        })
        .on('mouseout', function(d,i) {
          dispatch.legendMouseout(d,i);
        })
        .on('click', function(d,i) {
          dispatch.legendClick(d,i);
        });
      seriesEnter.append('circle')
        .style('stroke-width', 2)
        .attr('r', radius);
      seriesEnter.append('text')
        .style('stroke-width', 0)
        .style('stroke', 'inherit')
        .attr('text-anchor', 'start')
        .attr('dy', '.32em')
        .attr('dx', '8');
      series.classed('disabled', function(d) { return d.disabled; });
      series.exit().remove();
      series.select('circle')
        .attr('class', function(d,i) { return this.getAttribute('class') || classes(d,i); })
        .attr('fill', function(d,i) { return this.getAttribute('fill') || color(d,i); })
        .attr('stroke', function(d,i) { return this.getAttribute('fill') || color(d,i); });
      series.select('text').text(getKey);

      //------------------------------------------------------------

      //TODO: add ability to add key to legend
      //var label = g.append('text').text('Probability:').attr('class','nv-series-label').attr('transform','translate(0,0)');
      //TODO: implement fixed-width and max-width options (max-width is especially useful with the align option)

      if (equalColumns) {
        var keyWidths = []
          , keyCount = 0
          , keysPerRow = 0
          , columnWidths = []
          , computeWidth = function(prev, cur, index, array) {
              return prev + cur;
            };

        series.each(function(d,i) {
          keyWidths.push(d3.select(this).select('text').node().getComputedTextLength() + 2*radius + 3 + gutter); // 28 is ~ the width of the circle plus some padding
        });

        keyCount = keyWidths.length;
        keysPerRow = keyCount;
        legendWidth = keyWidths.reduce(computeWidth) - gutter;

        //keep decreasing the number of keys per row until
        //legend width is less than the available width
        while (keysPerRow > 1) {
          columnWidths = [];

          for (var k = 0, iCol = 0; k < keyCount; k += 1) {
            iCol = k % keysPerRow;
            if (keyWidths[k] > (columnWidths[iCol] || 0)) {
              columnWidths[iCol] = keyWidths[k];
            }
          }

          legendWidth = columnWidths.reduce(computeWidth) - gutter;

          if (legendWidth < availableWidth) {
            break;
          }

          keysPerRow -= 1;
        }

        if (Math.ceil(keyCount / keysPerRow) < 3) {

          var keyPositions = [];
          for (var i = 0, curX = radius; i < keysPerRow; i += 1) {
            keyPositions[i] = curX;
            curX += columnWidths[i];
          }

          height = margin.top + margin.bottom + radius * 2 + ((Math.ceil(keyCount / keysPerRow) - 1) * lineHeight);
          legendOpen = 0;

          zoom.on('zoom', null);

          clip
            .attr('x', 0 - margin.left)
            .attr('y', 0 - lineHeight + radius + 0.5)
            .attr('width', legendWidth + margin.right + margin.left)
            .attr('height', height);

          var offSet = 0.5 - margin.left;
          back
            .attr('x', align === 'right' ? width - legendWidth + offSet : align === 'center' ? offSet + (width - legendWidth) / 2 : offSet)
            .attr('width', legendWidth + margin.left + margin.right)
            .attr('height', height)
            .style('opacity', 0)
            .style('display', 'inline');

          //position legend as far right as possible within the total width
          mask
            .attr('transform', 'translate(' + (align === 'right' ? width - legendWidth : align === 'center' ? (width - legendWidth) / 2 : 0) + ',' + (margin.top + radius) + ')');

          g
            .style('opacity', 1)
            .style('display', 'inline');

          series
            .attr('transform', function(d, i) {
              return 'translate(' + keyPositions[i % keysPerRow] + ',' + (Math.floor(i / keysPerRow) * lineHeight) + ')';
            });

        } else {

          height = lineHeight + radius;
          legendWidth = d3.max(keyWidths) - gutter;
          legendHeight = margin.top + margin.bottom + radius * 2 + (keyCount - 1) * lineHeight;

          zoom.on('zoom', zoomLegend);

          clip
            .attr('x', 0 - margin.left)
            .attr('y', 0 - lineHeight + radius + 0.5)
            .attr('width', legendWidth + margin.right + margin.left)
            .attr('height', Math.min(availableHeight - height, legendHeight));

          back
            .attr('x', align === 'right' ? availableWidth - legendWidth + 0.5 : 0.5)
            .attr('y', lineHeight + radius + 0.5)
            .attr('width', legendWidth + margin.right + margin.left)
            .attr('height', Math.min(availableHeight - height, legendHeight))
            .style('opacity', legendOpen * 0.9)
            .style('display', legendOpen ? 'inline' : 'none')
            .call(zoom);

          link
            .style('opacity', 1);

          mask
            .attr('transform', 'translate(' + (align === 'right' ? width - margin.right - legendWidth : margin.left) + ',' + (margin.top + radius * 2 + lineHeight) + ')');

          g
            .style('opacity', legendOpen)
            .style('display', legendOpen ? 'inline' : 'none')
            .call(zoom);

          series
            .attr('transform', function(d, i) {
              return 'translate(' + radius + ',' + (i * lineHeight) + ')';
            });
        }

      } else {

        var xpos
          , ypos = radius
          , newxpos = radius;

        legendOpen = 0;

        series
          .attr('transform', function(d, i) {
            var length = d3.select(this).select('text').node().getComputedTextLength() + 2*radius + 3 + gutter;
            xpos = newxpos;

            if (availableWidth < xpos + length - gutter) {
              newxpos = xpos = radius;
              ypos += lineHeight;
            }

            newxpos += length;
            if (newxpos - gutter > legendWidth) {
              legendWidth = newxpos - gutter;
            }

            return 'translate(' + xpos + ',' + ypos + ')';
          });

        height = margin.top + margin.bottom + ypos + radius;

        //position legend as far right as possible within the total width
        g
          .attr('transform', 'translate(' + (width - margin.right - legendWidth) + ',' + margin.top + ')');

        back
          .attr('x', availableWidth - legendWidth + 0.5)
          .attr('width', legendWidth + margin.right + margin.left)
          .attr('height', margin.top + margin.bottom + radius + ypos);
      }

      dispatch.on('linkClick', function(d) {
        legendOpen = 1 - legendOpen;
        back
          .transition()
          .duration(200)
          .style('opacity', legendOpen * 0.9)
          .style('display', legendOpen ? 'inline' : 'none');
        g
          .transition()
          .duration(200)
          .style('opacity', legendOpen)
          .style('display', legendOpen ? 'inline' : 'none');
        link
          .text(legendOpen === 1 ? 'close' : 'legend');
      });

    });

    return legend;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  legend.dispatch = dispatch;

  legend.margin = function(_) {
    if (!arguments.length) { return margin; }
    margin.top    = typeof _.top    !== 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  !== 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom !== 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   !== 'undefined' ? _.left   : margin.left;
    return legend;
  };

  legend.width = function(_) {
    if (!arguments.length) { return width; }
    width = Math.round(_);
    return legend;
  };

  legend.height = function(_) {
    if (!arguments.length) { return height; }
    height = Math.round(_);
    return legend;
  };

  legend.id = function(_) {
    if (!arguments.length) { return id; }
    id = _;
    return legend;
  };

  legend.key = function(_) {
    if (!arguments.length) { return getKey; }
    getKey = _;
    return legend;
  };

  legend.color = function(_) {
    if (!arguments.length) { return color; }
    color = nv.utils.getColor(_);
    return legend;
  };

  legend.classes = function(_) {
    if (!arguments.length) { return classes; }
    classes = _;
    return legend;
  };

  legend.align = function(_) {
    if (!arguments.length) { return align; }
    align = _;
    return legend;
  };

  legend.equalColumns = function(_) {
    if (!arguments.length) { return equalColumns; }
    equalColumns = _;
    return legend;
  };

  legend.strings = function(_) {
    if (!arguments.length) { return strings; }
    strings = _;
    return legend;
  };
  //============================================================


  return legend;
};
