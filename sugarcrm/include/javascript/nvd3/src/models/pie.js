nv.models.pie = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 0, right: 0, bottom: 0, left: 0},
      width = 500,
      height = 500,
      getValues = function(d) { return d; },
      getX = function(d) { return d.key; },
      getY = function(d) { return d.value; },
      getDescription = function(d) { return d.description; },
      id = Math.floor(Math.random() * 10000), //Create semi-unique ID in case user doesn't select one
      valueFormat = d3.format(',.2f'),
      showLabels = true,
      showLeaders = true,
      pieLabelsOutside = true,
      donutLabelsOutside = true,
      labelThreshold = 0.02, //if slice percentage is under this, don't show label
      donut = false,
      labelSunbeamLayout = false,
      leaderLength = 10,
      startAngle = false,
      endAngle = false,
      donutRatio = 0.447,
      durationMs = 0,
      direction = 'ltr',
      color = function(d, i) { return nv.utils.defaultColor()(d, d.series); },
      fill = color,
      classes = function(d, i) { return 'nv-slice nv-series-' + d.series; },
      dispatch = d3.dispatch('chartClick', 'elementClick', 'elementDblClick', 'elementMouseover', 'elementMouseout', 'elementMousemove');

  //============================================================


  function chart(selection) {
    selection.each(function(data) {

      var availableWidth = width - margin.left - margin.right,
          availableHeight = height - margin.top - margin.bottom,
          container = d3.select(this);

      //------------------------------------------------------------
      // recalculate width and height based on label length
      var valuePadding = 0;
      if (showLabels && pieLabelsOutside) {
        valuePadding = nv.utils.maxStringSetLength(
            data.map(function(d) { return d.key; }),
            container,
            function(d) { return d; }
          );
        valuePadding = Math.round(valuePadding) + (showLeaders ? leaderLength + 5 : 0);
        valuePadding *= 2;
      }

      var radius = Math.min(availableWidth - valuePadding, availableHeight) / 2,
          arcRadius = radius - (showLabels && pieLabelsOutside ? 20 : 0);

      var arc = d3.svg.arc()
            .innerRadius(0)
            .outerRadius(arcRadius);

      if (startAngle) {
        arc.startAngle(startAngle);
      }
      if (endAngle) {
        arc.endAngle(endAngle);
      }
      if (donut) {
        arc.innerRadius(arcRadius * donutRatio);
      }

      var labelsArc = d3.svg.arc()
            .innerRadius(0)
            .outerRadius(arcRadius);

      if (pieLabelsOutside) {
        if (!donut || donutLabelsOutside) {
          labelsArc
            .innerRadius(radius)
            .outerRadius(radius);
        } else {
          labelsArc
            .outerRadius(arcRadius * donutRatio);
        }
      }
      //------------------------------------------------------------
      // Setup containers and skeleton of chart
      var wrap = container.selectAll('.nv-wrap.nv-pie').data([data]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-pie nv-chart-' + id);
      var defsEnter = wrapEnter.append('defs');
      var gEnter = wrapEnter.append('g');
      var g = wrap.select('g');

      //set up the gradient constructor function
      chart.gradient = function(d, i) {
        var params = {x: 0, y: 0, r: radius, s: (donut ? (donutRatio * 100) + '%' : '0%'), u: 'userSpaceOnUse'};
        return nv.utils.colorRadialGradient(d, id + '-' + i, params, color(d, i), wrap.select('defs'));
      };

      gEnter.append('g').attr('class', 'nv-pie');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');
      g.select('.nv-pie').attr('transform', 'translate(' + availableWidth / 2 + ',' + availableHeight / 2 + ')');

      //------------------------------------------------------------

      container
        .on('click', function(d, i) {
          dispatch.chartClick({
            data: d,
            index: i,
            pos: d3.event,
            id: id
          });
        });

      // Setup the Pie chart and choose the data element
      var pie = d3.layout.pie()
            .sort(null)
            .value(function(d) { return d.disabled ? 0 : getY(d); });

      var slices = wrap.select('.nv-pie').selectAll('.nv-slice')
            .data(pie);

      slices.exit().remove();

      var ae = slices.enter().append('g')
            .on('mouseover', function(d, i) {
              d3.select(this).classed('hover', true);
              dispatch.elementMouseover({
                label: getX(d.data),
                value: getY(d.data),
                point: d.data,
                pointIndex: i,
                pos: [d3.event.pageX, d3.event.pageY],
                id: id
              });
            })
            .on('mouseout', function(d, i) {
              d3.select(this).classed('hover', false);
              dispatch.elementMouseout({
                label: getX(d.data),
                value: getY(d.data),
                point: d.data,
                index: i,
                id: id
              });
            })
            .on('mousemove', function(d, i) {
              dispatch.elementMousemove({
                point: d,
                pointIndex: i,
                pos: [d3.event.pageX, d3.event.pageY],
                id: id
              });
            })
            .on('click', function(d, i) {
              dispatch.elementClick({
                label: getX(d.data),
                value: getY(d.data),
                point: d.data,
                index: i,
                pos: d3.event,
                id: id
              });
              d3.event.stopPropagation();
            })
            .on('dblclick', function(d, i) {
              dispatch.elementDblClick({
                label: getX(d.data),
                value: getY(d.data),
                point: d.data,
                index: i,
                pos: d3.event,
                id: id
              });
              d3.event.stopPropagation();
            });

      ae.append('path')
          .style('stroke', '#ffffff')
          .style('stroke-width', 3)
          .style('stroke-opacity', 1)
          .each(function(d) {
            this._current = d;
          });

      ae.append('g')
          .attr('transform', 'translate(0,0)')
          .attr('class', 'nv-label');

      ae.select('.nv-label')
          .append('rect')
          .style('fill-opacity', 0)
          .style('stroke-opacity', 0);
      ae.select('.nv-label')
          .append('text')
          .style('fill-opacity', 0);

      ae.append('polyline')
          .attr('class', 'nv-label-leader')
          .style('stroke-opacity', 0);

      slices
        .classed('nv-active', function(d) { return d.data.active === 'active'; })
        .classed('nv-inactive', function(d) { return d.data.active === 'inactive'; })
        .attr('class', function(d) { return classes(d.data, d.data.series); })
        .attr('fill', function(d) { return fill(d.data, d.data.series); });

      slices.select('path').transition().duration(durationMs)
        .attr('d', arc)
        .attrTween('d', arcTween);

      if (showLabels) {
        // This does the normal label
        slices.select('.nv-label')
          .attr('transform', function(d) {
            if (labelSunbeamLayout) {
              d.outerRadius = arcRadius + 10; // Set Outer Coordinate
              d.innerRadius = arcRadius + 15; // Set Inner Coordinate
              var rotateAngle = (d.startAngle + d.endAngle) / 2 * (180 / Math.PI);
              if ((d.startAngle + d.endAngle) / 2 < Math.PI) {
                rotateAngle -= 90;
              } else {
                rotateAngle += 90;
              }
              return 'translate(' + labelsArc.centroid(d) + ') rotate(' + rotateAngle + ')';
            } else {
              var labelsPosition = labelsArc.centroid(d),
                  alignedRight = (d.startAngle + d.endAngle) / 2 < Math.PI ? 1 : -1,
                  leadOffset = showLeaders ? (leaderLength + 5) * alignedRight : 0;
              return 'translate(' + [labelsPosition[0] + leadOffset, labelsPosition[1]] + ')';
            }
          });

        slices.select('.nv-label text')
          .text(function(d) {
            var percent = (d.endAngle - d.startAngle) / (2 * Math.PI),
                label = getX(d.data);
            return (label && percent > labelThreshold) ? label : '';
          })
          .attr('dy', '.35em')
          .style('fill', '#555')
          .style('fill-opacity', function(d) {
            var percent = (d.endAngle - d.startAngle) / (2 * Math.PI),
                label = getX(d.data);
            return (label && percent > labelThreshold) ? 1 : 0;
          })
          .style('text-anchor', function(d) {
            //center the text on it's origin or begin/end if orthogonal aligned
            //labelSunbeamLayout ? ((d.startAngle + d.endAngle) / 2 < Math.PI ? 'start' : 'end') : 'middle'
            var anchor = (d.startAngle + d.endAngle) / 2 < Math.PI ? 'start' : 'end';
            if (!pieLabelsOutside) {
              anchor = 'middle';
            }
            anchor = direction === 'rtl' ? anchor === 'start' ? 'end' : 'start' : anchor;
            return anchor;
          });

        if (!pieLabelsOutside) {
          slices.select('.nv-label')
            .each(function(d) {
              var slice = d3.select(this),
                  textBox = slice.select('text').node().getBBox();
              slice.select('rect')
                .attr('rx', 3)
                .attr('ry', 3)
                .attr('width', textBox.width + 10)
                .attr('height', textBox.height + 10)
                .attr('transform', function() {
                  return 'translate(' + [textBox.x - 5, textBox.y - 5] + ')';
                })
                .style('fill', '#fff')
                .style('fill-opacity', function(d) {
                  var percent = (d.endAngle - d.startAngle) / (2 * Math.PI),
                      label = getX(d.data);
                  return (label && percent > labelThreshold) ? 0.4 : 0;
                });
            });
        } else if (showLeaders) {
          slices.select('.nv-label-leader')
            .attr('points', function(d) {
              var alignedRight = (d.startAngle + d.endAngle) / 2 < Math.PI ? 1 : -1,
                  leadOffset = showLeaders ? leaderLength * alignedRight : 0,
                  outerArcPoints = d3.svg.arc()
                    .innerRadius(arcRadius)
                    .outerRadius(arcRadius)
                    .centroid(d),
                  labelsArcPoints = labelsArc.centroid(d),
                  leadArcPoints = [labelsArcPoints[0] + leadOffset, labelsArcPoints[1]];
              return outerArcPoints + ' ' + labelsArcPoints + ' ' + leadArcPoints;
            })
            .style('stroke', '#aaa')
            .style('fill', 'none')
            .style('stroke-opacity', function(d) {
              var percent = (d.endAngle - d.startAngle) / (2 * Math.PI),
                  label = getX(d.data);
              return (label && percent > labelThreshold) ? 1 : 0;
            });
        }
      } else {
        slices.select('.nv-label-leader').style('stroke-opacity', 0);
        slices.select('.nv-label rect').style('fill-opacity', 0);
        slices.select('.nv-label text').style('fill-opacity', 0);
      }

      // Computes the angle of an arc, converting from radians to degrees.
      function angle(d) {
        var a = (d.startAngle + d.endAngle) * 90 / Math.PI - 90;
        return a > 90 ? a - 180 : a;
      }

      function arcTween(d) {
        if (!donut) {
          d.innerRadius = 0;
        }
        var i = d3.interpolate(this._current, d);
        this._current = i(0);

        return function(t) {
          var iData = i(t);
          return arc(iData);
        };
      }

      function tweenPie(b) {
        b.innerRadius = 0;
        var i = d3.interpolate({startAngle: 0, endAngle: 0}, b);
        return function(t) {
          return arc(i(t));
        };
      }

    });

    return chart;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  chart.dispatch = dispatch;

  chart.color = function(_) {
    if (!arguments.length) {
      return color;
    }
    color = _;
    return chart;
  };
  chart.fill = function(_) {
    if (!arguments.length) {
      return fill;
    }
    fill = _;
    return chart;
  };
  chart.classes = function(_) {
    if (!arguments.length) {
      return classes;
    }
    classes = _;
    return chart;
  };
  chart.gradient = function(_) {
    if (!arguments.length) {
      return gradient;
    }
    gradient = _;
    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) {
      return margin;
    }
    margin.top    = typeof _.top    != 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  != 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom != 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   != 'undefined' ? _.left   : margin.left;
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) {
      return width;
    }
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) {
      return height;
    }
    height = _;
    return chart;
  };

  chart.values = function(_) {
    if (!arguments.length) {
      return getValues;
    }
    getValues = _;
    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) {
      return getX;
    }
    getX = _;
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) {
      return getY;
    }
    getY = d3.functor(_);
    return chart;
  };

  chart.description = function(_) {
    if (!arguments.length) {
      return getDescription;
    }
    getDescription = _;
    return chart;
  };

  chart.showLabels = function(_) {
    if (!arguments.length) {
      return showLabels;
    }
    showLabels = _;
    return chart;
  };

  chart.labelSunbeamLayout = function(_) {
    if (!arguments.length) {
      return labelSunbeamLayout;
    }
    labelSunbeamLayout = _;
    return chart;
  };

  chart.donutLabelsOutside = function(_) {
    if (!arguments.length) {
      return donutLabelsOutside;
    }
    donutLabelsOutside = _;
    return chart;
  };

  chart.pieLabelsOutside = function(_) {
    if (!arguments.length) {
      return pieLabelsOutside;
    }
    pieLabelsOutside = _;
    return chart;
  };

  chart.showLeaders = function(_) {
    if (!arguments.length) {
      return showLeaders;
    }
    showLeaders = _;
    return chart;
  };

  chart.donut = function(_) {
    if (!arguments.length) {
      return donut;
    }
    donut = _;
    return chart;
  };

  chart.donutRatio = function(_) {
    if (!arguments.length) {
      return donutRatio;
    }
    donutRatio = _;
    return chart;
  };

  chart.startAngle = function(_) {
    if (!arguments.length) {
      return startAngle;
    }
    startAngle = _;
    return chart;
  };

  chart.endAngle = function(_) {
    if (!arguments.length) {
      return endAngle;
    }
    endAngle = _;
    return chart;
  };

  chart.id = function(_) {
    if (!arguments.length) {
      return id;
    }
    id = _;
    return chart;
  };

  chart.valueFormat = function(_) {
    if (!arguments.length) {
      return valueFormat;
    }
    valueFormat = _;
    return chart;
  };

  chart.labelThreshold = function(_) {
    if (!arguments.length) {
      return labelThreshold;
    }
    labelThreshold = _;
    return chart;
  };

  chart.direction = function(_) {
    if (!arguments.length) {
      return direction;
    }
    direction = _;
    return chart;
  };

  //============================================================

  return chart;
}
