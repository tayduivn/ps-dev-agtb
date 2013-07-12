
nv.models.paretoChart = function () {
  //'use strict';
  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 10, right: 20, bottom: 40, left: 40}
    , width = null
    , height = null
    , getX = function (d) { return d.x; }
    , getY = function (d) { return d.y; }
    , showControls = true
    , showLegend = true
    , showTitle = false
    , reduceXTicks = false // if false a tick will show for every data point
    , reduceYTicks = false // if false a tick will show for every data point
    , rotateLabels = 0
    //, rotateLabels = -15
    , tooltips = true
    , tooltipBar = function (key, x, y, e, graph) {
        return '<p>Stage: <b>' + key + '</b></p>' +
               '<p>Amount: <b>' +  y + '</b></p>' +
               '<p>Percent: <b>' +  x + '%</b></p>';
      }
    , tooltipLine = function (key, x, y, e, graph) {
        return '<p>Likely: <b>' + y + '</b></p>';
      }
    //, x //can be accessed via chart.xScale()
    //, y //can be accessed via chart.yScale()
    , noData = 'No Data Available.'
    ;

  var multibar = nv.models.multiBar().stacked(true)
    //, x = d3.scale.linear() // needs to be both line and historicalBar x Axis
    , x = multibar.xScale()
    , lines = nv.models.line()
    , y = multibar.yScale()
    , xAxis = nv.models.axis().scale(x).orient('bottom').tickPadding(10)
    , yAxis = nv.models.axis().scale(y).orient('left').tickPadding(10).showMaxMin(false)

    , barLegend = nv.models.paretoLegend()
    , lineLegend = nv.models.paretoLegend()
    , controls = nv.models.legend()
    , dispatch = d3.dispatch('tooltipShow', 'tooltipHide')
    ;

  xAxis
    .highlightZero(false)
    .showMaxMin(false)
    ;

  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var showTooltip = function (e, offsetElement, dataGroup, lOffset) {
    var containerPosition = getAbsoluteXY(offsetElement)
      , left = e.pos[0] + (containerPosition.x || 0) + (e.series.type === 'bar' ? 0 : lOffset)
      , top = e.pos[1] + (containerPosition.y || 0)
      , per = (e.point.y * 100 / dataGroup[e.pointIndex].t).toFixed(1)
      , amt = yAxis.tickFormat()(lines.y()(e.point, e.pointIndex))
      , content = (e.series.type === 'bar' ? tooltipBar(e.series.key, per, amt, e, chart) : tooltipLine(e.series.key, per, amt, e, chart));

    nv.tooltip.show([left, top], content, 's', null, offsetElement);
  };

  var barClick = function (data,e,selection) {
    //if only one bar series is disabled
    if (data.filter(function (d) { return !d.disabled && d.type === 'bar'; }).length === 1) {
      // reenable the disabled bar series
      data = data.map(function (d) {
        d.disabled = false;
        return d;
      });
    } else {
      // hide the selected bar series
      data = data.filter(function (d) { return d.type === 'bar'; }).map(function (d, i) {
        d.disabled = (i !== e.seriesIndex);
        return d;
      });
    }
    container.transition().duration(300).call(chart);
  };

  var getAbsoluteXY = function (element) {
    var viewportElement = document.documentElement
      , box = element.getBoundingClientRect()
      , scrollLeft = viewportElement.scrollLeft + document.body.scrollLeft
      , scrollTop = viewportElement.scrollTop + document.body.scrollTop
      , x = box.left + scrollLeft
      , y = box.top + scrollTop;

    return {"x": x, "y": y};
  };
  //============================================================


  function chart(selection) {

    selection.each(function (chartData) {

      var properties = chartData.properties
        , data = chartData.data;

      var container = d3.select(this),
          that = this;

      //var expandMode = container.node().parentNode.className.indexOf('expanded') !== -1;
      var expandMode = false;

      margin.left = (expandMode) ? 50 : 60;
      margin.bottom = (expandMode) ? 40 : 34;

      var availableWidth = (width  || parseInt(container.style('width'), 10) || 960) - margin.left - margin.right
        , availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom
        , availableLegend = (width  || parseInt(container.style('width'), 10) || 960) - margin.right - margin.right;

      chart.update = function () { container.transition().duration(300).call(chart); };
      chart.container = this;


      //------------------------------------------------------------
      // Display noData message if there's nothing to show.

      if (!data || !data.length || !data.filter(function(d) { return d.values.length; }).length) {
        var noDataText = container.selectAll('.nv-noData').data([noData]);

        noDataText.enter().append('text')
          .attr('class', 'nvd3 nv-noData')
          .attr('dy', '-.7em')
          .style('text-anchor', 'middle');

        noDataText
          .attr('x', margin.left + availableWidth / 2)
          .attr('y', margin.top + availableHeight / 2)
          .text(function(d) { return d; });

        return chart;
      } else {
        container.selectAll('.nv-noData').remove();
      }

      //------------------------------------------------------------


      var dataBars = data.filter(function (d) { return !d.disabled && d.type === 'bar'; })
        , dataLines = data.filter(function (d) { return !d.disabled && d.type === 'line'; })
        , dataGroup = properties.groupData
        , quotaValue = properties.quota;

      //TODO: try to remove x scale computation from this layer
      // var series1 = data.filter(
      //       function(d) {
      //         return !d.disabled && d.type==='bar'
      //       }
      //     ).map(
      //       function(d) {
      //         return d.values.map(
      //           function(d,i) {
      //             return { x: getX(d,i), y: getY(d,i) }
      //           }
      //         )
      //       }
      //   );
      var seriesX = data.filter(
            function (d) {
              return !d.disabled;
            }
          ).map(
            function (d) {
              return d.valuesOrig.map(
                function (d,i) {
                  return getX(d,i);
                }
              );
            }
        );

      var seriesY = data.filter(
            function (d) {
              return !d.disabled;
            }
          ).map(
            function (d) {
              return d.valuesOrig.map(
                function (d,i) {
                  return getY(d,i);
                }
              );
            }
        );

      var lx = x.domain(d3.merge(seriesX)).rangeBands([0, availableWidth], 0.3)
        , ly = Math.max(d3.max(d3.merge(seriesY)), quotaValue)
        , forceY = Math.round((ly + ly * 0.1) * 0.1) * 10
        , lOffset = lx(1) + lx.rangeBand() / (multibar.stacked() || dataLines.length === 1 ? 2 : 4);

      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-multiBarWithLegend').data([data])
        , gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-multiBarWithLegend').append('g')
        , g = wrap.select('g');

      gEnter.append('g').attr('class', 'nv-x nv-axis');
      gEnter.append('g').attr('class', 'nv-y nv-axis');
      gEnter.append('g').attr('class', 'nv-barsWrap');
      gEnter.append('g').attr('class', 'nv-linesWrap1');
      gEnter.append('g').attr('class', 'nv-linesWrap2');
      gEnter.append('g').attr('class', 'nv-quotaWrap');

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Title & Legend

      var titleHeight = 0
        , legendHeight = 0
        , wideLegend = multibar.stacked() && dataBars.length > 2
        , quotaLegend = {'key':'Quota', 'type':'dash', 'color':'#444', 'values':{'series':0,'x':0,'y':0}};

      if (showLegend) {

        // bar series legend
        gEnter.append('g').attr('class', 'nv-legendWrap nv-barLegend');
        barLegend.width(availableLegend);
        g.select('.nv-legendWrap.nv-barLegend')
            .datum(
              //data
              data.filter(function (d) {
                //return !d.disabled
                return d.type === 'bar';
              })
            )
            .call(barLegend);


        // line series legend
        gEnter.append('g').attr('class', 'nv-legendWrap nv-lineLegend');
        lineLegend.width(availableLegend);
        g.select('.nv-legendWrap.nv-lineLegend')
            .datum(
              data.filter(function (d) {
                return d.type === 'line';
              }).concat([quotaLegend])
            )
            .call(lineLegend);


        // bar legend data
        var barKeyWidths = [];
        var barLegendKeys = g.select('.nv-legendWrap.nv-barLegend').selectAll('.nv-series');
        barLegendKeys.select('text').each( function (d,i) {
          barKeyWidths.push(d3.select(this).node().getComputedTextLength()); // 28 is ~ the width of the circle plus some padding
        });
        var barMaxKeyWidth = d3.max(barKeyWidths);
        var barColsPerLegend = 0;

        // line legend data
        var lineKeyWidths = [];
        var lineLegendKeys = g.select('.nv-legendWrap.nv-lineLegend').selectAll('.nv-series');
        lineLegendKeys.select('text').each( function (d,i) {
          lineKeyWidths.push(d3.select(this).node().getComputedTextLength()); // 28 is ~ the width of the circle plus some padding
        });
        var lineMaxKeyWidth = d3.max(lineKeyWidths);
        var lineColsPerLegend = 0;

        // calculate max keys per legend
        var colCount = barKeyWidths.length + lineKeyWidths.length;
        var legendWidth = 0;

        for (var i = 0; i < colCount; i += 1) {
          if (legendWidth < availableLegend) {

            // nv.log('legendWidth',legendWidth)
            // nv.log('barMaxKeyWidth',barMaxKeyWidth)
            // nv.log('legendWidth + barMaxKeyWidth',legendWidth + barMaxKeyWidth < availableLegend);
            // nv.log('barKeyWidths.length',barKeyWidths.length);
            // nv.log('lineColsPerLegend % lineKeyWidths.length',lineColsPerLegend % lineKeyWidths.length);
            // nv.log('barColsPerLegend % barKeyWidths.length',barColsPerLegend % barKeyWidths.length);

            if (
                legendWidth + barMaxKeyWidth < availableLegend &&
                (barColsPerLegend <= lineColsPerLegend || lineColsPerLegend === lineKeyWidths.length) &&
                barColsPerLegend < barKeyWidths.length
                //barColsPerLegend % barKeyWidths.length <= lineColsPerLegend % lineKeyWidths.length
            ) {
              barColsPerLegend += 1;
              legendWidth += barMaxKeyWidth;
            } else if (
              legendWidth + lineMaxKeyWidth < availableLegend &&
              lineColsPerLegend < lineKeyWidths.length
            ) {
              lineColsPerLegend += 1;
              legendWidth += lineMaxKeyWidth;
            }
          } else {
            break;
          }
        }

        barLegendKeys.attr('transform', function (d,i) {
          return 'translate(' + barMaxKeyWidth * (i % barColsPerLegend) + ',' + (5 + Math.floor(i / barColsPerLegend) * 35) + ')';
        });
        lineLegendKeys.attr('transform', function (d,i) {
          return 'translate(' + lineMaxKeyWidth * (i % lineColsPerLegend) + ',' + (5 + Math.floor(i / lineColsPerLegend) * 35) + ')';
        });


        barLegend.height(Math.ceil(barKeyWidths.length / barColsPerLegend) * 35);
        lineLegend.height(Math.ceil(lineKeyWidths.length / lineColsPerLegend) * 35);

        //calculate position
        legendHeight = Math.max(barLegend.height(), lineLegend.height()) + 15;


        g.select('.nv-legendWrap.nv-barLegend')
            .attr('transform', 'translate('+ (barMaxKeyWidth/2 - margin.left) +','+ (-legendHeight) +')');

        g.select('.nv-legendWrap.nv-lineLegend')
            .attr('transform', 'translate(' + (availableWidth - g.select('.nv-legendWrap.nv-lineLegend').node().getBBox().width) +','+ (-legendHeight) +')');

        if (margin.top !== legendHeight + titleHeight) {
          margin.top = legendHeight + titleHeight + 15;
          availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom;
        }

        // nv.log('barColsPerLegend',barColsPerLegend);
        // nv.log('lineColsPerLegend',lineColsPerLegend);
        // nv.log('availableWidth',availableWidth);
        // nv.log('availableLegend',availableLegend);
        // nv.log('legendWidth',legendWidth);

      }

      if (showTitle && properties.title) {
        gEnter.append('g').attr('class', 'nv-titleWrap');

        g.select('.nv-title').remove();

        g.select('.nv-titleWrap')
          .append('text')
            .attr('class', 'nv-title')
            .attr('x', 0)
            .attr('y', 0)
            .attr('text-anchor', 'start')
            .text(properties.title)
            .attr('stroke', 'none')
            .attr('fill', 'black')
          ;

        titleHeight = parseInt(g.select('.nv-title').node().getBBox().height, 10) +
          parseInt(g.select('.nv-title').style('margin-top'), 10) +
          parseInt(g.select('.nv-title').style('margin-bottom'), 10);

        if (margin.top !== titleHeight + legendHeight) {
          margin.top = titleHeight + legendHeight;
          availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom;
        }

        g.select('.nv-titleWrap')
            .attr('transform', 'translate(0,'+ (-margin.top + parseInt(g.select('.nv-title').node().getBBox().height, 10)) +')');
      }

      //------------------------------------------------------------
      // Controls

      if (showControls) {
        gEnter.append('g').attr('class', 'nv-controlsWrap');

        var controlsData = [
          { key: 'Grouped', disabled: multibar.stacked() },
          { key: 'Stacked', disabled: !multibar.stacked() }
        ];

        controls.width(availableWidth * 0.3).color(['#444']);

        g.select('.nv-controlsWrap')
            .datum(controlsData)
            .attr('transform', 'translate(0,'+ (-margin.top + titleHeight) +')')
            .call(controls);
      }

      //------------------------------------------------------------

      wrap.attr('transform', 'translate('+ margin.left +','+ margin.top +')');


      //------------------------------------------------------------
      // Main Chart Component(s)

      multibar
        .width(availableWidth)
        .height(availableHeight)
        .forceY([0,forceY])
        .id(chart.id());

      lines
        .margin({top: 0, right: lOffset, bottom: 0, left: lOffset})
        .width(availableWidth)
        .height(availableHeight)
        .forceY([0,forceY])
        .id(chart.id());

      var barsWrap = g.select('.nv-barsWrap')
          .datum(dataBars.length ? dataBars : [{values: []}]);

      var linesWrap1 = g.select('.nv-linesWrap1')
          .datum(
            dataLines.length ? dataLines.map(function (d) {
                d.values = (!multibar.stacked()) ? d.valuesOrig.map(function (v,i) {
                  return {'series': v.series, 'x': (v.x + v.series * 0.25 - i * 0.25), 'y': v.y};
                }) : d.valuesOrig;
                return d;
              }) : [{values:[]}]
          );
      var linesWrap2 = g.select('.nv-linesWrap2')
          .datum(
            dataLines.length ? dataLines.map(function (d) {
                d.values = (!multibar.stacked()) ? d.valuesOrig.map(function (v,i) {
                  return {'series': v.series, 'x': (v.x + v.series * 0.25 - i * 0.25), 'y': v.y};
                }) : d.valuesOrig;
                return d;
              }) : [{values:[]}]
          );
      barsWrap.call(multibar);
      //.selectAll('rect.nv-bar').each(function(d){ console.log(this); } );
      linesWrap1.call(lines);
      linesWrap2.call(lines);
      linesWrap1.selectAll('path').style('stroke-width',8).style('stroke','#FFFFFF');
      linesWrap2.transition().selectAll('circle').attr('r',8).style('stroke','#FFFFFF');
      linesWrap2.transition().selectAll('path').style('stroke-width',4);
      //barsWrap;
      //------------------------------------------------------------
      // Quota Line

      if (quotaValue)
      {
        g.selectAll('line.nv-quotaLine').remove();

        g.select('.nv-quotaWrap').append('line')
          .attr('class', 'nv-quotaLine')
          .attr('x1', 0)
          .attr('y1', 0)
          .attr('x2', availableWidth)
          .attr('y2', 0)
          .attr('transform', 'translate(0,'+ y(quotaValue) +')')
          .style('stroke-dasharray','8, 8')
          .style('stroke-width','4px');
      }

      //------------------------------------------------------------
      // Setup Axes

      xAxis
        .ticks(availableWidth / 100)
        .tickSize(0)
        .tickFormat(function(d,i) {
          return dataGroup[i] ? dataGroup[i].l : 'asfd';
        });

      g.select('.nv-x.nv-axis')
          .attr('transform', 'translate(0,'+ y.range()[0] +')');
      g.select('.nv-x.nv-axis').transition()
          .call(xAxis);

      var xTicks = g.select('.nv-x.nv-axis > g').selectAll('g');

      xTicks
        .selectAll('line, text')
        .style('opacity', 1);

      xTicks.select('text').each(function (d) {

        var textContent = this.textContent
          , textNode = d3.select(this)
          , textArray = textContent.split(' ')
          , l = textArray.length
          , i = 0
          , dy = 0.71
          , maxWidth = x.rangeBand();

        if (this.getBBox().width > maxWidth)
        {
          this.textContent = '';

          do
          {
            var textString
              , textSpan = textNode.append('tspan')
                  .text(textArray[i] +' ')
                  .attr('dy', dy +'em')
                  .attr('x', 0 +'px');

            if (i === 0)
            {
              dy = 0.9;
            }

            i += 1;

            while (i < l)
            {
              textString = textSpan.text();
              textSpan.text(textString +' '+ textArray[i]);
              if (this.getBBox().width <= maxWidth)
              {
                i += 1;
              }
              else
              {
                textSpan.text(textString);
                break;
              }
            }

          }
          while (i < l);
        }

      });

      if (reduceXTicks) {
        xTicks
          .filter(function (d,i) {
              return i % Math.ceil(data[0].values.length / (availableWidth / 100)) !== 0;
            })
          .selectAll('text, line')
          .style('opacity', 0);
      }
      if (rotateLabels) {
        xTicks
          .selectAll('text')
          .attr('transform', function (d,i,j) { return 'rotate('+ rotateLabels +' 0,0) translate(0,10)'; })
          .attr('text-transform', rotateLabels > 0 ? 'start' : 'end');
      }

      yAxis
        .ticks(availableHeight / 100)
        .tickSize(-availableWidth, 0)
        .tickFormat(function (d) { return '$'+ d3.format(',.2s')(d); });

      g.select('.nv-y.nv-axis').transition()
          .style('opacity', dataBars.length ? 1 : 0)
          .call(yAxis);


      // Quota line label
      g.selectAll('text.nv-quotaValue').remove();
      g.select('.nv-y.nv-axis').append('text')
          .attr('class', 'nv-quotaValue')
          .text('$'+ d3.format(',.2s')(quotaValue))
          .attr('dy', '.36em')
          .attr('dx', '0')
          .attr('text-anchor','end')
          .attr('transform', 'translate(-10,'+ y(quotaValue) +')');


      //------------------------------------------------------------


      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      barLegend.dispatch.on('legendClick', function (d,i) {
        var selectedSeries = d.series;
        d.disabled = !d.disabled;
        data.filter(function(d){
            return d.series === selectedSeries && d.type === 'line';
          }).map(function(d) {
            d.disabled = !d.disabled;
            return d;
          });
        // if there are no enabled data series, enable them all
        if ( !data.filter(function(d) { return !d.disabled && d.type === 'bar'; }).length )
        {
          data.map(function(d) {
            d.disabled = false;
            wrap.selectAll('.nv-series').classed('disabled', false);
            return d;
          });
        }
        container.transition().duration(300).call(chart);
      });

      controls.dispatch.on('legendClick', function (d,i) {
        if (!d.disabled) {
          return;
        }
        controlsData = controlsData.map(function (s) {
          s.disabled = true;
          return s;
        });
        d.disabled = false;

        switch (d.key) {
          case 'Grouped':
            multibar.stacked(false);
            break;
          case 'Stacked':
            multibar.stacked(true);
            break;
        }

        container.transition().duration(300).call(chart);
      });

      /*dispatch.on('tooltipShow', function(e) {
        if (tooltips) showTooltip(e, that.parentNode)
      });*/

      lines.dispatch.on('elementMouseover.tooltip', function (e) {
        e.pos = [e.pos[0] +  margin.left, e.pos[1] + margin.top];
        dispatch.tooltipShow(e);
      });

      lines.dispatch.on('elementMouseout.tooltip', function (e) {
        dispatch.tooltipHide(e);
      });


      multibar.dispatch.on('elementMouseover.tooltip', function (e) {
        e.pos = [e.pos[0] +  margin.left, e.pos[1] + margin.top];
        dispatch.tooltipShow(e);
      });

      multibar.dispatch.on('elementMouseout.tooltip', function (e) {
        dispatch.tooltipHide(e);
      });

      multibar.dispatch.on('elementClick', function (e) {
        barClick(data,e,selection);
      });

      if (tooltips) {
        dispatch.on('tooltipShow', function (e) {
          showTooltip(e, that.parentNode, dataGroup, lOffset);
        }); // TODO: maybe merge with above?
      }
      if (tooltips) {
        dispatch.on('tooltipHide', nv.tooltip.cleanup);
      }

      //============================================================

    });

    return chart;
  }


  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  /*multibar.dispatch.on('elementMouseover.tooltip2', function(e) {
    e.pos = [e.pos[0] +  margin.left, e.pos[1] + margin.top];
    dispatch.tooltipShow(e);
  });

  multibar.dispatch.on('elementMouseout.tooltip', function(e) {
    dispatch.tooltipHide(e);
  });
  dispatch.on('tooltipHide', function() {
    if (tooltips) nv.tooltip.cleanup();
  });*/

  //============================================================


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.lines = lines;
  chart.multibar = multibar;
  chart.barLegend = barLegend;
  chart.lineLegend = lineLegend;
  chart.xAxis = xAxis;
  chart.yAxis = yAxis;

  d3.rebind(chart, multibar, 'x', 'y', 'xDomain', 'yDomain', 'forceX', 'forceY', 'clipEdge', 'id', 'stacked', 'delay', 'color', 'fill', 'gradient', 'classes');

  chart.colorData = function (_) {
    var colors = function (d,i) { return nv.utils.defaultColor()(d,i); },
        classes = function (d,i) { return 'nv-group nv-series-' + i; },
        type = arguments[0],
        params = arguments[1] || {};

    switch (type) {
      case 'graduated':
        var c1 = params.c1
          , c2 = params.c2
          , l = params.l;
        colors = function (d,i) { return d3.interpolateHsl( d3.rgb(c1), d3.rgb(c2) )(i/l); };
        break;
      case 'class':
        colors = function () { return 'inherit'; };
        classes = function (d,i) {
          var iClass = (i*(params.step || 1))%20;
          return 'nv-group nv-series-'+ i +' '+ ( d.classes || 'nv-fill'+ (iClass>9?'':'0') + iClass );
        };
        break;
    }

    var fill = (!params.gradient) ? colors : function (d,i) {
      var p = {orientation: params.orientation || 'vertical', position: params.position || 'middle'};
      return multibar.gradient(d,i,p);
    };

    multibar.color(colors);
    multibar.fill(fill);
    multibar.classes(classes);

    lines.color( function (d,i) { return d3.interpolateHsl( d3.rgb('#1a8221'), d3.rgb('#62b464') )(i/1); } );
    lines.fill( function (d,i) { return d3.interpolateHsl( d3.rgb('#1a8221'), d3.rgb('#62b464') )(i/1); } );
    lines.classes(classes);

    barLegend.color(colors);
    barLegend.classes(classes);

    lineLegend.color(function (d,i) { return d3.interpolateHsl( d3.rgb('#1a8221'), d3.rgb('#62b464') )(i/1); });
    lineLegend.classes(classes);

    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) { return getX; }
    getX = _;
    lines.x(_);
    multibar.x(_);
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) { return getY; }
    getY = _;
    lines.y(_);
    multibar.y(_);
    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) { return margin; }
    margin = _;
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) { return width; }
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) { return height; }
    height = _;
    return chart;
  };

  chart.showControls = function(_) {
    if (!arguments.length) { return showControls; }
    showControls = _;
    return chart;
  };

  chart.showLegend = function(_) {
    if (!arguments.length) { return showLegend; }
    showLegend = _;
    return chart;
  };

  chart.showTitle = function(_) {
    if (!arguments.length) { return showTitle; }
    showTitle = _;
    return chart;
  };

  chart.reduceXTicks= function(_) {
    if (!arguments.length) { return reduceXTicks; }
    reduceXTicks = _;
    return chart;
  };

  chart.rotateLabels = function(_) {
    if (!arguments.length) { return rotateLabels; }
    rotateLabels = _;
    return chart;
  };

  chart.tooltipBar = function(_) {
    if (!arguments.length) { return tooltipBar; }
    tooltipBar = _;
    return chart;
  };

  chart.tooltipLine = function(_) {
    if (!arguments.length) { return tooltipLine; }
    tooltipLine = _;
    return chart;
  };

  chart.tooltips = function(_) {
    if (!arguments.length) { return tooltips; }
    tooltips = _;
    return chart;
  };

  chart.tooltipContent = function(_) {
    if (!arguments.length) { return tooltip; }
    tooltip = _;
    return chart;
  };

  chart.noData = function(_) {
    if (!arguments.length) { return noData; }
    noData = _;
    return chart;
  };

  chart.barClick = function(_) {
    if (!arguments.length) { return barClick; }
    barClick = _;
    return chart;
  };

  chart.tooltip = function(_) {
    if (!arguments.length) return tooltip;
    tooltip = _;
    return chart;
  };

  chart.colorFill = function(_) {
    return chart;
  };

  //============================================================


  return chart;
};
