
nv.models.paretoChart = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 30, right: 20, bottom: 20, left: 40}//margin = {top: 30, right: 20, bottom: 50, left: 60}
    , width = null
    , height = null
    , getX = function(d) { return d.x }
    , getY = function(d) { return d.y }
    , color = nv.utils.defaultColor()
    , showControls = true
    , showLegend = true
    , showTitle = false
    , reduceXTicks = false // if false a tick will show for every data point
    , reduceYTicks = false // if false a tick will show for every data point
    , rotateLabels = 0
    //, rotateLabels = -15
    , tooltips = true
    , tooltipBar = function(key, x, y, e, graph) {
        return '<p>Stage: <b>' + key + '</b></p>' +
               '<p>Amount: <b>' +  y + '</b></p>' +
               '<p>Percent: <b>' +  x + '%</b></p>'
      }
    , tooltipLine = function(key, x, y, e, graph) {
        return '<p>Likely: <b>' + y + '</b></p>'
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
    , xAxis = nv.models.axis().scale(x).orient('bottom').tickPadding(5)
    , yAxis = nv.models.axis().scale(y).orient('left')

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

  var showTooltip = function(e, offsetElement, dataGroup, lOffset) {
    var containerPosition = getAbsoluteXY(offsetElement)
      , left = e.pos[0] + ( containerPosition.x || 0 ) + (e.series.type==='bar'?0:lOffset)
      , top = e.pos[1] + ( containerPosition.y || 0)
      , per = (e.point.y * 100 / dataGroup[e.pointIndex].t).toFixed(1)
      , amt = yAxis.tickFormat()( lines.y()(e.point, e.pointIndex) )
      , content = ( e.series.type==='bar' ? tooltipBar(e.series.key, per, amt, e, chart) : tooltipLine(e.series.key, per, amt, e, chart) );

    nv.tooltip.show([left, top], content, 's', null, offsetElement);
  };

  var barClick = function(data,e,selection) {
    //if only one bar series is disabled
    if (data.filter(function(d) { return !d.disabled && d.type==='bar' }).length === 1)
      // reenable the disabled bar series
      data = data.map(function(d) {
        d.disabled = false;
        return d
      });
    else
      // hide the selected bar series
      data = data.filter(function(d) { return d.type==='bar' }).map( function(d,i) {
        d.disabled = (i != e.seriesIndex);
        return d
      });

    selection.transition().call(chart);
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

    selection.each(function(chartData) {

      var properties = chartData.properties
        , data = chartData.data;

      var container = d3.select(this),
          that = this;

      var availableWidth = (width  || parseInt(container.style('width')) || 960)
                             - margin.left - margin.right,
          availableHeight = (height || parseInt(container.style('height')) || 400)
                             - margin.top - margin.bottom;

      //------------------------------------------------------------
      // Display noData message if there's nothing to show.

      if (!data || !data.length || !data.filter(function(d) { return d.values.length }).length) {
        container.append('text')
          .attr('class', 'nvd3 nv-noData')
          .attr('x', availableWidth / 2)
          .attr('y', availableHeight / 2)
          .attr('dy', '-.7em')
          .style('text-anchor', 'middle')
          .text(noData);
          return chart;
      } else {
        container.select('.nv-noData').remove();
      }

      //------------------------------------------------------------


      var dataBars = data.filter(function(d) { return !d.disabled && d.type==='bar' })
        , dataLines = data.filter(function(d) { return !d.disabled && d.type==='line' })
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
            function(d) {
              return !d.disabled
            }
          ).map(
            function(d) {
              return d.valuesOrig.map(
                function(d,i) {
                  return getX(d,i)
                }
              )
            }
        );

      var seriesY = data.filter(
            function(d) {
              return !d.disabled
            }
          ).map(
            function(d) {
              return d.valuesOrig.map(
                function(d,i) {
                  return getY(d,i)
                }
              )
            }
        );

      var lx = x.domain(d3.merge(seriesX)).rangeBands([0, availableWidth], .3)
        , ly = Math.max(d3.max(d3.merge(seriesY)),quotaValue)
        , forceY = Math.round( (ly + ly*.1) * .1 )*10
        , lOffset = lx(1) + lx.rangeBand()/(multibar.stacked()||dataLines.length===1?2:4);

      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-multiBarWithLegend').data([data])
        , gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-multiBarWithLegend').append('g')
        , g = wrap.select('g');

      gEnter.append('g').attr('class', 'nv-x nv-axis');
      gEnter.append('g').attr('class', 'nv-y nv-axis');
      gEnter.append('g').attr('class', 'nv-barsWrap');
      gEnter.append('g').attr('class', 'nv-linesWrap');
      gEnter.append('g').attr('class', 'nv-quotaWrap');

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Title & Legend

      var titleHeight = 0
        , legendHeight = 0
        , wideLegend = multibar.stacked() && dataBars.length > 2;

      if (showLegend)
      {
        // bar series legend
        gEnter.append('g').attr('class', 'nv-legendWrap nv-barLegend');

        barLegend.width( availableWidth * ( wideLegend ? 0.75 : 0.5 ) );

        g.select('.nv-legendWrap.nv-barLegend')
            .datum(
              //data
              data.filter(function(d) {
                //return !d.disabled
                return d.type==='bar';
              })
            )
            .call(barLegend);

        // line series legend
        gEnter.append('g').attr('class', 'nv-legendWrap nv-lineLegend');

        lineLegend.width(availableWidth*( wideLegend ? 0.25 : 0.4 ));

        g.select('.nv-legendWrap.nv-lineLegend')
            .datum(
              data.filter(function(d) {
                return d.type==='line';
              }).concat([{'key':'Quota ($'+d3.format(',.2s')(quotaValue)+')','type':'line','color':'#444'}])
            )
            .call(lineLegend);

        //calculate position
        legendHeight = Math.max(barLegend.height(),lineLegend.height());

        if ( margin.top !== legendHeight + titleHeight ) {
          margin.top = legendHeight + titleHeight;
          availableHeight = (height || parseInt(container.style('height')) || 400)
                             - margin.top - margin.bottom;
        }

        g.select('.nv-legendWrap.nv-barLegend')
            .attr('transform', 'translate('+(availableWidth*( wideLegend ? 0.25 : 0.4 ))+',' + (-margin.top) +')');
        g.select('.nv-legendWrap.nv-lineLegend')
            .attr('transform', 'translate(0,' + (-margin.top) +')');
      }

      if (showTitle && properties.title )
      {
        gEnter.append('g').attr('class', 'nv-titleWrap');

        g.select('.nv-title').remove();

        g.select('.nv-titleWrap')
          .append('text')
            .attr('class', 'nv-title')
            .attr('x', 0)
            .attr('y', 0 )
            .attr('text-anchor', 'start')
            .text(properties.title)
            .attr('stroke', 'none')
            .attr('fill', 'black')
          ;

        titleHeight = parseInt( g.select('.nv-title').style('height') ) +
          parseInt( g.select('.nv-title').style('margin-top') ) +
          parseInt( g.select('.nv-title').style('margin-bottom') );

        if ( margin.top !== titleHeight + legendHeight )
        {
          margin.top = titleHeight + legendHeight;
          availableHeight = (height || parseInt(container.style('height')) || 400)
                             - margin.top - margin.bottom;
        }

        g.select('.nv-titleWrap')
            .attr('transform', 'translate(0,' + (-margin.top+parseInt( g.select('.nv-title').style('height') )) +')');
      }

      //------------------------------------------------------------
      // Controls

      if (showControls)
      {
        gEnter.append('g').attr('class', 'nv-controlsWrap');

        var controlsData = [
          { key: 'Grouped', disabled: multibar.stacked() },
          { key: 'Stacked', disabled: !multibar.stacked() }
        ];

        controls.width(availableWidth*0.3).color(['#444']);

        g.select('.nv-controlsWrap')
            .datum(controlsData)
            .attr('transform', 'translate(0,' + (-margin.top+titleHeight) +')')
            .call(controls);
      }

      //------------------------------------------------------------

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');


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
          .datum( dataBars.length ? dataBars : [{values:[]}] );

      var linesWrap = g.select('.nv-linesWrap')
          .datum(
            dataLines.length
            ? dataLines.map(function(d){
                d.values = (!multibar.stacked()) ? d.valuesOrig.map(function(v,i){
                  return {'series':v.series,'x':(v.x+v.series*0.25-i*0.25),'y':v.y};
                }) : d.valuesOrig;
                return d;
              })
            : [{values:[]}]
          );

      d3.transition(barsWrap).call(multibar);
      d3.transition(linesWrap).call(lines);

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
          .attr('transform', 'translate(0,' + y(quotaValue) +')')
          .style('stroke-dasharray','20, 5');
      }

      //------------------------------------------------------------
      // Setup Axes

      xAxis
        .ticks( availableWidth / 100 )
        .tickSize(0)
        .tickFormat(function(d,i) {
          return dataGroup[i] ? dataGroup[i].l : 'asfd'
        });

      g.select('.nv-x.nv-axis')
          .attr('transform', 'translate(0,' + y.range()[0] + ')');
      d3.transition(g.select('.nv-x.nv-axis'))
          .call(xAxis);

      var xTicks = g.select('.nv-x.nv-axis > g').selectAll('g');

      xTicks
        .selectAll('line, text')
        .style('opacity', 1)

      if (reduceXTicks)
        xTicks
          .filter(function(d,i) {
              return i % Math.ceil(data[0].values.length / (availableWidth / 100)) !== 0;
            })
          .selectAll('text, line')
          .style('opacity', 0);

      if (rotateLabels)
      {
        xTicks
          .selectAll('text')
          .attr('transform', function(d,i,j) { return 'rotate('+rotateLabels+' 0,0) translate(0,10)' })
          .attr('text-transform', rotateLabels > 0 ? 'start' : 'end');
      }

      yAxis
        .ticks( availableHeight / 100 )
        .tickSize(-availableWidth, 0)
        .tickFormat(function(d){ return '$' + d3.format(',.2s')(d) });

      d3.transition(g.select('.nv-y.nv-axis'))
          .style('opacity', dataBars.length ? 1 : 0)
          .call(yAxis);

      //------------------------------------------------------------


      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      barLegend.dispatch.on('legendClick', function(d,i) {
        var selectedSeries = d.series;
        d.disabled = !d.disabled;
        data.filter(function(d){
            return d.series===selectedSeries && d.type==='line';
          }).map(function(d) {
            d.disabled = !d.disabled;
            return d;
          });
        // if there are no enabled data series, enable them all
        if ( !data.filter(function(d){return !d.disabled && d.type==='bar';}).length )
        {
          data.map(function(d) {
            d.disabled = false;
            wrap.selectAll('.nv-series').classed('disabled', false);
            return d;
          });
        }
        selection.transition().call(chart);
      });

      controls.dispatch.on('legendClick', function(d,i) {
        if (!d.disabled) return;
        controlsData = controlsData.map(function(s) {
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

        selection.transition().call(chart);
      });

      /*dispatch.on('tooltipShow', function(e) {
        if (tooltips) showTooltip(e, that.parentNode)
      });*/

      lines.dispatch.on('elementMouseover.tooltip', function(e) {
        e.pos = [e.pos[0] +  margin.left, e.pos[1] + margin.top];
        dispatch.tooltipShow(e);
      });

      lines.dispatch.on('elementMouseout.tooltip', function(e) {
        dispatch.tooltipHide(e);
      });


      multibar.dispatch.on('elementMouseover.tooltip', function(e) {
        e.pos = [e.pos[0] +  margin.left, e.pos[1] + margin.top];
        dispatch.tooltipShow(e);
      });

      multibar.dispatch.on('elementMouseout.tooltip', function(e) {
        dispatch.tooltipHide(e);
      });

      multibar.dispatch.on('elementClick', function(e) {
        barClick(data,e,selection);
      });

      if (tooltips) dispatch.on('tooltipShow', function(e) { showTooltip(e, that.parentNode, dataGroup, lOffset) } ); // TODO: maybe merge with above?
      if (tooltips) dispatch.on('tooltipHide', nv.tooltip.cleanup);

      //============================================================


      chart.update = function() { selection.transition().call(chart) };
      chart.container = this; // I need a reference to the container in order to have outside code check if the chart is visible or not

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

  d3.rebind(chart, multibar, 'x', 'y', 'xDomain', 'yDomain', 'forceX', 'forceY', 'clipEdge', 'id', 'stacked', 'delay', 'color', 'gradient', 'useClass');

  d3.rebind(chart, lines, 'color');
  //consider rebinding x and y as well

  chart.colorData = function(_) {
    if (arguments[0] === 'graduated')
    {
      var c1 = arguments[1].c1
        , c2 = arguments[1].c2
        , l = arguments[1].l;
      var color = function (d,i) { return d3.interpolateHsl( d3.rgb(c1), d3.rgb(c2) )(i/l) };
    }
    else if (_ === 'class')
    {
      chart.useClass(true);
      barLegend.useClass(true);
      lineLegend.useClass(true);
      var color = function (d,i) { return 'inherit' };
    }
    else
    {
      var color = nv.utils.defaultColor();
    }

    barLegend.color(color);
    lineLegend.color(color);
    multibar.color(color);
    lines.color(color);

    return chart;
  };

  chart.colorFill = function(_) {
    if (_ === 'gradient')
    {
      var fill = function (d,i) { return chart.gradient()(d,i) };
    }
    else
    {
      var fill = chart.color();
    }

    multibar.fill(fill);

    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) return getX;
    getX = _;
    lines.x(_);
    multibar.x(_);
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) return getY;
    getY = _;
    lines.y(_);
    multibar.y(_);
    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) return margin;
    margin = _;
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) return width;
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) return height;
    height = _;
    return chart;
  };

  chart.showControls = function(_) {
    if (!arguments.length) return showControls;
    showControls = _;
    return chart;
  };

  chart.showLegend = function(_) {
    if (!arguments.length) return showLegend;
    showLegend = _;
    return chart;
  };

  chart.showTitle = function(_) {
    if (!arguments.length) return showTitle;
    showTitle = _;
    return chart;
  };

  chart.reduceXTicks= function(_) {
    if (!arguments.length) return reduceXTicks;
    reduceXTicks = _;
    return chart;
  };

  chart.rotateLabels = function(_) {
    if (!arguments.length) return rotateLabels;
    rotateLabels = _;
    return chart;
  }

  chart.tooltipBar = function(_) {
    if (!arguments.length) return tooltipBar;
    tooltipBar = _;
    return chart;
  };

  chart.tooltipLine = function(_) {
    if (!arguments.length) return tooltipLine;
    tooltipLine = _;
    return chart;
  };

  chart.tooltips = function(_) {
    if (!arguments.length) return tooltips;
    tooltips = _;
    return chart;
  };

  chart.tooltipContent = function(_) {
    if (!arguments.length) return tooltip;
    tooltip = _;
    return chart;
  };

  chart.noData = function(_) {
    if (!arguments.length) return noData;
    noData = _;
    return chart;
  };

  chart.barClick = function(_) {
    if (!arguments.length) return barClick;
    barClick = _;
    return chart;
  };

  //============================================================


  return chart;
}
